<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Dependency
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Dependency;

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Dependency
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.4 23-jan-2015 10:34:59
 */
class CallbackDependency extends DependencyAbstract
{
    /**
     *
     * @var callable
     */
    protected $_callback;

    /**
     * Applies the result of the callback
     *
     * @param callback $callback A callback function
     * @param mixed $targetName  A fieldname or array of field names set to the output of the function
     * @param mixed $targetKey   Optional fields set by callback, otherwise callback should return array
     * @param mixed $sourceName  Optional parameter input names for the callback, passed in order
     * @throws \MUtil\Model\Dependency\DependencyException
     */
    public function __construct($callback, $targetName, $targetKey = null, $sourceName = null)
    {
        if (!is_callable($callback)) {
            throw new DependencyException(__CLASS__ . " requires a valid callback.");
        }

        $this->_callback = $callback;

        $this->_dependentOn = $sourceName;
        if (is_array($targetName)) {
            $this->_effecteds = array_fill_keys($targetName, (array) $targetKey);
        } else {
            $this->_effecteds[$targetName] = (array) $targetKey;
        }

        parent::__construct();
    }

    /**
     * Returns the changes that must be made in an array consisting of
     *
     * <code>
     * array(
     *  field1 => array(setting1 => $value1, setting2 => $value2, ...),
     *  field2 => array(setting3 => $value3, setting4 => $value4, ...),
     * </code>
     *
     * By using [] array notation in the setting name you can append to existing
     * values.
     *
     * Use the setting 'value' to change a value in the original data.
     *
     * When a 'model' setting is set, the workings cascade.
     *
     * @param array $context The current data this object is dependent on
     * @param boolean $new True when the item is a new record not yet saved
     * @return array name => array(setting => value)
     */
    public function getChanges(array $context, bool $new = false): array
    {
        $args = array();
        foreach ($this->getDependsOn() as $name) {
            $args[] = isset($context[$name]) ? $context[$name] : null;
        }
        $changes = call_user_func_array($this->_callback, $args);

        $output = array();
        foreach ($this->getEffecteds() as $name => $settings) {
            if (! $settings) {
                $output[$name] = $changes;
            } else {
                $output[$name] = array_fill_keys($settings, $changes);
            }
        }
        return $output;
    }
}
