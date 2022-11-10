<?php

/**
 *
 * @package    MUtil
 * @subpackage Model_Bridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Bridge;

use MUtil\Model\Bridge\LazyBridgeFormat;
use Zalt\Late\RepeatableInterface;
use Zalt\Model\Bridge\BridgeInterface;

    /**
 *
 *
 * @package    MUtil
 * @subpackage Model_Bridge
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since 2014 $(date} 22:00:02
 */
abstract class BridgeAbstract extends \MUtil\Translate\TranslateableAbstract
    implements \MUtil\Model\Bridge\BridgeInterface
{
    /**
     * Mode when all output is lazy until rendering
     */
    const MODE_LAZY = 0;

    /**
     * Mode when all rows are preloaded using model->load()
     */
    const MODE_ROWS = 1;

    /**
     * Mode when only a single row is loaded using model->loadFirst()
     */
    const MODE_SINGLE_ROW = 2;

    /**
     *
     * @var \MUtil\Model\Bridge\BridgeAbstract
     */
    protected $_chainedBridge;

    /**
     * Field name => compiled result, i.e. array of functions to call with only the value as parameter
     *
     * @var array
     */
    protected $_compilations = array();

    /**
     * Nested array or single row, depending on mode
     *
     * @var array
     */
    protected $_data;

    /**
     * A lazy repeater
     *
     * @var \Zalt\Late\RepeatableInterface
     */
    protected $_repeater;

    /**
     * Omde of the self::MODE constants
     *
     * @var int
     */
    protected $mode = self::MODE_LAZY;

    /**
     *
     * @var \MUtil\Model\ModelAbstract
     */
    protected $model;

    /**
     * Construct the bridge while setting the model.
     *
     * Extra parameters can be added in subclasses, but the first parameter
     * must remain the model.
     *
     * @param \MUtil\Model\ModelAbstract $model
     */
    public function __construct(\Zalt\Model\Data\DataReaderInterface $model)
    {
        $this->setModel($model);
    }

    /**
     * Returns a formatted value or a lazy call to that function,
     * depending on the mode.
     *
     * @param string $name The field name or key name
     * @return mixed Lazy unless in single row mode
     * @throws \MUtil\Model\ModelException
     */
    public function __get(string $name): mixed
    {
        return $this->getFormatted($name);
    }

    /**
     * Checks name for being a key id field and in that case returns the real field name
     *
     * @param string $name The field name or key name
     * @param boolean $throwError By default we throw an error until rendering
     * @return string The real name and not e.g. the key id
     * @throws \MUtil\Model\ModelException
     */
    protected function _checkName($name, $throwError = true)
    {
        if ($this->model->has($name)) {
            return $name;
        }

        $modelKeys = $this->model->getKeys();
        if (isset($modelKeys[$name])) {
            return $modelKeys[$name];
        }

        if ($throwError) {
            throw new \MUtil\Model\ModelException(
                    sprintf('Request for unknown item %s from model %s.', $name, $this->model->getName())
                    );
        }

        return $name;
    }

    /**
     * Return an array of functions used to process the value
     *
     * @param string $name The real name and not e.g. the key id
     * @return array
     */
    abstract protected function _compile($name);

    /**
     * Format a value using the rules for the specified name.
     *
     * This is the workhouse function for the foematter and can
     * also be used with data not loaded from the model.
     *
     * To add the raw value to the called function as raw parameter, use an array callback for function,
     * and add a temporary third value of true.
     *
     * @param string $name The real name and not e.g. the key id
     * @param mixed $value
     * @return mixed
     */
    public function format($name, $value)
    {
        if (! array_key_exists($name, $this->_compilations)) {
            if ($this->_chainedBridge) {
                $this->_compilations[$name] = array_merge(
                        $this->_chainedBridge->_compile($name),
                        $this->_compile($name)
                        );
            } else {
                $this->_compilations[$name] = $this->_compile($name);
            }
        }

        $raw = $value;
        foreach ($this->_compilations[$name] as $function) {
            if (is_array($function) && isset($function[2])) {
                // Check if raw should be added to the current callback
                $rawMode = array_pop($function);
                if ($rawMode) {
                    $value = call_user_func($function, $value, $raw);
                    continue;
                }
            }

            $value = call_user_func($function, $value);

        }

        return $value;
    }

    /**
     * Returns a formatted value or a lazy call to that function,
     * depending on the mode.
     *
     * @param string $name The field name or key name
     * @return mixed Lazy unless in single row mode
     * @throws \MUtil\Model\ModelException
     */
    public function getFormatted(string $name): mixed
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        $fieldName = $this->_checkName($name);

        // Make sure the field is in the trackUsage fields list
        $this->model->get($fieldName);

        if ((self::MODE_SINGLE_ROW === $this->mode) && isset($this->_data[$fieldName])) {
            $this->$name = $this->format($fieldName, $this->_data[$fieldName]);
        } else {
            $this->$name = new LazyBridgeFormat($this, $fieldName);
        }
        if ($fieldName !== $name) {
            $this->model->get($name);
            $this->$fieldName = $this->$name;
        }

        return $this->$name;
    }

    /**
     * Return the lazy value without any processing.
     *
     * @param string $name The field name or key name
     * @return \MUtil\Lazy\Call
     */
    public function getLazy($name)
    {
        // Make sure data is loaded
        $this->model->get($name);

        return \MUtil\Lazy::call(array($this, 'getLazyValue'), $name);
    }

    /**
     * Get the repeater result for
     *
     * @param string $name The field name or key name
     * @return mixed The result for name
     */
    public function getLazyValue($name)
    {
        $name = $this->_checkName($name, false);

        if (! $this->_repeater) {
            $this->getRepeater();
        }

        $current = $this->_repeater->__current();
        if ($current && isset($current[$name])) {
            return $current[$name];
        }
        
        return null;
    }

    /**
     * Get the mode to one of Lazy (works with any other mode), one single row or multi row mode.
     *
     * @return int On of the MODE_ constants
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     *
     * @return \Zalt\Model\Data\DataReaderInterface
     */
    public function getModel(): \Zalt\Model\Data\DataReaderInterface
    {
        return $this->model;
    }

    /**
     * Get the repeater source for the lazy data
     *
     * @return \Zalt\Late\RepeatableInterface
     */
    public function getRepeater(): \Zalt\Late\RepeatableInterface
    {
        if (! $this->_repeater) {
            if ($this->_chainedBridge && $this->_chainedBridge->hasRepeater()) {
                $this->setRepeater($this->_chainedBridge->getRepeater());
            } else {
                $this->setRepeater($this->model->loadRepeatable());
            }
        }

        return $this->_repeater;
    }

    /**
     * Switch to single row mode and return that row.
     *
     * @return array or false when no row was found
     * @throws \MUtil\Model\ModelException
     */
    public function getRow()
    {
        $this->setMode(self::MODE_SINGLE_ROW);

        if (! is_array($this->_data)) {
            $this->setRow();
        }

        return $this->_data;
    }

    /**
     * Switch to multi rows mode and return that the rows.
     *
     * @return array Nested or empty when no rows were found
     * @throws \MUtil\Model\ModelException
     */
    public function getRows()
    {
        $this->setMode(self::MODE_ROWS);

        if (! is_array($this->_data)) {
            $this->setRows();
        }

        return $this->_data;
    }

    /**
     *
     * @param string $name
     * @return mixed Lazy unless in single row mode
     */
    public function getValue($name)
    {
        $name = $this->_checkName($name);

        if ((self::MODE_SINGLE_ROW === $this->mode) && isset($this->_data[$name])) {
            return $this->_data[$name];
        }

        return $this->getLazy($name);
    }

    /**
     * Returns true if name is in the model
     *
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        if ($this->model->has($name)) {
            return true;
        }

        $modelKeys = $this->model->getKeys();
        return (boolean) isset($modelKeys[$name]);
    }

    /**
     * is there a repeater source for the lazy data
     *
     * @return boolean
     */
    public function hasRepeater(): bool
    {
        return $this->_repeater instanceof \MUtil\Lazy\RepeatableInterface ||
                ($this->_chainedBridge && $this->_chainedBridge->hasRepeater());
    }

    /**
     * Set the mode to one of Lazy (works with any other mode), one single row or multi row mode.
     *
     * @param int $mode On of the MODE_ constants
     * @return \Zalt\Model\Bridge\BridgeInterface (continuation pattern)
     * @throws \Zalt\Model\Exceptions\MetaModelException The mode can only be set once
     */
    public function setMode(int $mode): BridgeInterface
    {
        if (($mode == $this->mode) || (self::MODE_LAZY == $this->mode)) {
            $this->mode = $mode;

            if ($this->_chainedBridge) {
                $this->_chainedBridge->mode = $this->mode;
            }

            return $this;
        }

        throw new \MUtil\Model\ModelException("Illegal bridge mode set after mode had already been set.");
    }

    /**
     * Set the model to be used by the bridge.
     *
     * This method exist to allow overruling in implementation classes
     *
     * @param \MUtil\Model\ModelAbstract $model
     * @return \MUtil\Model\Bridge\BridgeAbstract (continuation pattern)
     */
    public function setModel(\MUtil\Model\ModelAbstract $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set the repeater source for the lazy data
     *
     * @param mixed $repeater \MUtil\Lazy\RepeatableInterface or something that can be made into one.
     * @return BridgeInterface (continuation pattern)
     */
    public function setRepeater($repeater): BridgeInterface
    {
        if (! $repeater instanceof \MUtil\Lazy\RepeatableInterface) {
            $repeater = new \MUtil\Lazy\Repeatable($repeater);
        }
        $this->_repeater = $repeater;
        if ($this->_chainedBridge) {
            $this->_chainedBridge->_repeater = $repeater;
        }

        return $this;
    }

    /**
     * Switch to single row mode and set that row.
     *
     * @param array $row Or load from model
     * @throws \MUtil\Model\ModelException
     */
    public function setRow(array $row = null)
    {
        $this->setMode(self::MODE_SINGLE_ROW);

        if (null === $row) {
            // Stop tracking usage, in row mode it is unlikely
            // all fields have been set.
            $this->model->trackUsage(false);
            $row = $this->model->loadFirst();

            if (! $row) {
                $row = array();
            }
        }

        $this->_data = $row;
        if ($this->_chainedBridge) {
            $this->_chainedBridge->_data = $this->_data;
        }

        $this->setRepeater(array($this->_data));

        return $this;
    }

    /**
     * Switch to multi rows mode and set those rows.
     *
     * @param array $rows Or load from model
     * @throws \MUtil\Model\ModelException
     */
    public function setRows(array $rows = null)
    {
        $this->setMode(self::MODE_ROWS);

        if (null === $rows) {
            if ($this->_repeater) {
                $rows = $this->_repeater->__getRepeatable();
            } else {
                $rows = $this->model->load();
            }

            if (! $rows) {
                $rows = array();
            }
        }

        $this->_data = $rows;
        if ($this->_chainedBridge) {
            $this->_chainedBridge->_data = $this->_data;
        }

        $this->setRepeater($this->_data);

        return $this;
    }
}
