<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Transform;

use Zalt\Model\MetaModelInterface;

/**
 * Transforms the output of a model->load() function to include required rows.
 *
 * A good usage example is a time report, when there has to be an output row for e.g.
 * every week, even when there is no data for that week.
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class RequiredRowsTransformer extends \MUtil\Model\ModelTransformerAbstract
{
    /**
     * Contains default values for all missing row values
     *
     * @var mixed Something that can be made into an array using \MUtil\Ra::to()
     */
    protected $_defaultRow;

    /**
     * The number of key values to compare, if empty the number of fields in the first required row
     *
     * @var int
     */
    protected $_keyItemCount;

    /**
     *
     * @var mixed Something that can be made into an array using \MUtil\Ra::to()
     */
    protected $_requiredRows;

    /**
     *
     * @param array $required
     * @param array $row
     * @param int $count
     * @return boolean True if the rows refer to the same row
     */
    protected function _compareRows($required, $row, $count)
    {
        if ($row) {
            $val1 = reset($required);
            $key  = key($required);
            $val2 = $row[$key];
            $i = 0;
            while ($i < $count) {
                if ($val1 != $val2) {
                    return false;
                }
                $val1 = next($required);
                $val2 = next($row);
                $i++;
            }
            return true;

        } else {
            return false;
        }
    }

    /**
     * Returns the required rows set or calculates the rows using the $model and the required rows info
     *
     * @param \MUtil\Model\ModelAbstract $model Optional model for calculation
     * @return array
     * @throws \MUtil\Model\ModelException
     */
    public function getDefaultRow(\MUtil\Model\ModelAbstract $model = null)
    {
        if (! $this->_defaultRow) {
            $requireds = $this->getRequiredRows();
            $required  = \MUtil\Ra::to(reset($requireds));

            if (! $this->_keyItemCount) {
                $this->setKeyItemCount(count($required));
            }

            if ($required && ($model instanceof \MUtil\Model\ModelAbstract)) {
                $defaults = array();
                foreach ($model->getItemNames() as $name) {
                    if (! array_key_exists($name, $required)) {
                        $defaults[$name] = null;
                    }
                }
                $this->_defaultRow = $defaults;
            } else {
                throw new \MUtil\Model\ModelException('Cannot create default row without model and required rows.');
            }
        } elseif (! is_array($this->_defaultRow)) {
            $this->_defaultRow = \MUtil\Ra::to($this->_defaultRow);
        }

        return $this->_defaultRow;
    }

    /**
     * The number of key values to compare
     *
     * @return int
     */
    public function getKeyItemCount()
    {
        if (! $this->_keyItemCount) {
            $required = \MUtil\Ra::to(reset($this->getRequiredRows()));
            $this->setKeyItemCount(count($required));
        }

        return $this->_keyItemCount;
    }

    /**
     * Array of required rows
     *
     * @return array
     */
    public function getRequiredRows()
    {
        if (! is_array($this->_requiredRows)) {
            $this->_requiredRows = \MUtil\Ra::to($this->_requiredRows);
        }

        return $this->_requiredRows;
    }

    /**
     * Contains default values for all missing row values
     *
     * @param mixed $defaultRow Something that can be made into an array using \MUtil\Ra::to()
     * @return \MUtil\Model\Transform\RequiredRowsTransformer
     * @throws \MUtil\Model\ModelException
     */
    public function setDefaultRow($defaultRow)
    {
        if (\MUtil\Ra::is($defaultRow)) {
            $this->_defaultRow = $defaultRow;
            return $this;
        }

        throw new \MUtil\Model\ModelException('Invalid parameter type for ' . __FUNCTION__ . ': $rows cannot be converted to an array.');
    }

    /**
     * The number of key values to compare
     *
     * @param int $count
     * @return \MUtil\Model\Transform\RequiredRowsTransformer
     */
    public function setKeyItemCount($count)
    {
        $this->_keyItemCount = $count;
        return $this;
    }

    /**
     * The keys for the required rows
     *
     * @param mixed $rows Something that can be made into an array using \MUtil\Ra::to()
     * @return \MUtil\Model\Transform\RequiredRowsTransformer
     * @throws \MUtil\Model\ModelException
     */
    public function setRequiredRows($rows)
    {
        if (\MUtil\Ra::is($rows)) {
            $this->_requiredRows = $rows;
            return $this;
        }

        throw new \MUtil\Model\ModelException('Invalid parameter type for ' . __FUNCTION__ . ': $rows cannot be converted to an array.');
    }

    /**
     * The transform function performs the actual transformation of the data and is called after
     * the loading of the data in the source model.
     *
     * @param \MUtil\Model\ModelAbstract $model The parent model
     * @param array $data Nested array
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     * @return array Nested array containing (optionally) transformed data
     */
    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false)
    {
        $defaults  = $this->getDefaultRow($model);
        $keyCount  = $this->getKeyItemCount();
        $requireds = $this->getRequiredRows();
        $data      = \MUtil\Ra::to($data, \MUtil\Ra::RELAXED);
        $results   = array();
        if (! $data) {
            foreach ($requireds as $key => $required) {
                $results[$key] = $required + $defaults;
            }
        } else {
            $row = reset($data);
            foreach ($requireds as $key => $required) {
                if ($this->_compareRows($required, $row, $keyCount)) {
                    $results[$key] = $row + $required;
                    $row = next($data);
                } else {
                    $results[$key] = $required + $defaults;
                }
            }
        }

        return $results;
    }
}

