<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Transform;

use Zalt\Model\MetaModelInterface;

/**
 * Add one or more totals lines to the output, either for the whole or on changes in values of a field.
 *
 * Functions that can be used to sum are:
 *
 * - count: the number of rows counted
 * - sum: just add the total
 * - last: use the last value that occured
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.4 2-jan-2015 17:17:34
 */
class SumTotalTransformer extends \MUtil\Model\ModelTransformerAbstract
{
    /**
     *
     * @var array of suummarizeField => ['array' => [fieldName => values], 'string' => [fieldName => value]]
     */
    private $_summarizeOn = array();

    /**
     * Helper function for total rows field calculation
     *
     * @param striong $keyField
     * @param mixed $keyValue
     * @param array $currentValues
     */
    protected function _calculateFixedValues($keyField, $keyValue, array &$currentValues)
    {
        if (isset($this->_summarizeOn[$keyField]['arrays'])) {
            foreach ($this->_summarizeOn[$keyField]['arrays'] as $targetField => $lookup) {
                if (isset($lookup[$keyValue])) {
                    $currentValues[$targetField] = $lookup[$keyValue];
                }
            }
        }
        if (isset($this->_summarizeOn[$keyField]['values'])) {
            foreach ($this->_summarizeOn[$keyField]['values'] as $targetField) {
                $currentValues[$targetField] = $keyValue;
            }
        }
        if (isset($this->_summarizeOn[$keyField]['calls'])) {
            foreach ($this->_summarizeOn[$keyField]['calls'] as $targetField => $function) {
                $value = isset($currentValues[$targetField]) ? $currentValues[$targetField] : null;
                $currentValues[$targetField] = call_user_func($function, $value, $targetField);
            }
        }
        if (isset($this->_summarizeOn[$keyField]['string'])) {
            foreach ($this->_summarizeOn[$keyField]['string'] as $targetField => $fixed) {
                $currentValues[$targetField] = $fixed;
            }
        }
    }

    /**
     * Add a field to add a totals row on.
     *
     * The other parameters contains fixed field values for that row, e.g. a fixed value:
     *
     * <code>
     * $transformer->addTotal('groupField', 'rowClass', 'total');
     * </code>
     *
     * or a lookup array:
     *
     * <code>
     * $transformer->addTotal('groupField', 'labelField', array('x' => 'Total for X', 'y' => 'Total for Y'));
     * </code>
     *
     * or a callable:
     *
     * <code>
     * $transformer->addTotal('groupField', 'labelField', function ($value, $keyField) {sprintf('Total %d', $value);});
     * </code>
     *
     * for as many fields as required.
     *
     * @param type $field
     * @param type $fixedFieldsArrayOrName1
     * @param type $fixedFieldsValue1
     * @return \MUtil\Model\Transform\SumTotalTransformer
     */
    public function addTotal($field, $fixedFieldsArrayOrName1 = null, $fixedFieldsValue1 = null)
    {
        $args  = \MUtil\Ra::pairs(func_get_args(), 1);
        $fixed = array();

        foreach ($args as $fixedName => $value) {
            if (is_callable($value)) {
                $fixed['calls'][$fixedName] = $value;
            } elseif (is_array($value)) {
                $fixed['arrays'][$fixedName] = $value;
            } elseif (true === $value) {
                $fixed['values'][$fixedName] = $fixedName;
            } else {
                $fixed['string'][$fixedName] = $value;
            }
            // Make sure the fields are known to the model
            $this->_fields[$fixedName] = array();
        }

        $this->_summarizeOn[$field] = $fixed;

        return $this;
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
        if (! $data) {
            return $data;
        }

        $output        = array();
        $keyValues     = array();
        $summarizeCols = $model->getCol('summaryFunction');
        $sumReset      = array_fill_keys(array_keys($summarizeCols), 0) +
                array_fill_keys(array_keys(reset($data)), null);
        $sumValues     = array_fill_keys(array_keys($this->_summarizeOn), $sumReset);

        foreach ($data as $row) {
            // Add summarize rows to output when the dependent value has changed
            foreach ($sumValues as $keyField => $currentValues) {
                if (isset($sumValues[$keyField], $row[$keyField]) &&
                        array_key_exists($keyField, $keyValues) &&
                        ($row[$keyField] !== $keyValues[$keyField])) {

                    $this->_calculateFixedValues($keyField, $keyValues[$keyField], $currentValues);

                    $output[] = $currentValues;
                }
            }
            // Output the current row itself
            $output[] = $row;

            // Calculate the new values for the summarised rows
            foreach ($sumValues as $keyField => $currentValues) {
                if (array_key_exists($keyField, $row)) {
                    // Create summarize rows
                    if ((!array_key_exists($keyField, $keyValues)) || ($row[$keyField] != $keyValues[$keyField])) {
                        $keyValues[$keyField] = $row[$keyField];
                        $currentValues        = $sumReset;
                    }
                }
                // Calculate summarize values
                foreach ($summarizeCols as $fieldName => $function) {
                    if (array_key_exists($fieldName, $row) && array_key_exists($fieldName, $currentValues)) {
                        switch ($function) {
                            case 'sum':
                                $currentValues[$fieldName] = $currentValues[$fieldName] + $row[$fieldName];
                                break;

                            case 'count':
                                $currentValues[$fieldName]++;
                                break;

                            case 'last':
                                $currentValues[$fieldName] = $row[$fieldName];
                                break;

                            default:
                                break;
                        }
                    }
                }
                $sumValues[$keyField] = $currentValues;
            }
        }

        foreach ($sumValues as $keyField => $currentValues) {
            $keyValue = isset($keyValues[$keyField]) ? $keyValues[$keyField] : null;

            $this->_calculateFixedValues($keyField, $keyValue, $currentValues);

            $output[] = $currentValues;
        }

        return $output;
    }
}
