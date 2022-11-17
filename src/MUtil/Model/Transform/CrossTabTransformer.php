<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Transform;

use Zalt\Model\MetaModelInterface;

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.2
 */
class CrossTabTransformer extends \MUtil\Model\ModelTransformerAbstract
{
    /**
     * The fields to crosstab over
     *
     * @var array Nested array: index => array('id' => idField, 'val' => valueField, 'pre' => prefix)
     */
    protected $crossTabs;

    /**
     * The fields to exclude from the crosstab result
     *
     * Calculated by setCrosstabFields
     *
     * @var array idField => idField
     */
    protected $excludes;

    /**
     * Set the idField / crossTab output fields for the transformer.
     *
     * You can define multiple crossTabs over the same id value.
     *
     * @param string $idField    The field values to perform the crosstab over
     * @param string $valueField The field values to crosstab
     * @param string $prefix     Optional prefix to add before the $idField value as the identifier
     *                           for the output field, otherwise
     * @return \MUtil\Model\Transform\CrossTabTransformer (continuation pattern)
     */
    public function addCrosstabField($idField, $valueField, $prefix = null)
    {
        if (null === $prefix) {
            $prefix = $valueField . '_';
        }

        $this->crossTabs[] = array(
            'id'  => $idField,
            'val' => $valueField,
            'pre' => $prefix,
            );

        $this->excludes[$idField]    = $idField;
        $this->excludes[$valueField] = $valueField;

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

        //*
        $row = reset($data);
        if (! ($this->crossTabs)) {
            return $data;
        }

        $keys    = $model->getKeys();
        $keys    = array_combine($keys, $keys);
        $default = array_fill_keys(array_keys(array_diff_key($this->_fields, $this->excludes)), null);
        $results = array();
        // \MUtil\EchoOut\EchoOut::track($default);

        foreach ($data as $row) {
            foreach ($this->crossTabs as $crossTab) {
                $name = $crossTab['pre'] . $row[$crossTab['id']];

                $key = implode("\t", array_intersect_key($row, $keys));

                if (! isset($results[$key])) {
                    $results[$key] = array_diff_key($row, $this->excludes) + $default;
                }

                $results[$key][$name] = $row[$crossTab['val']];
            }
        }

        if (\MUtil\Model::$verbose) {
            \MUtil\EchoOut\EchoOut::r($results, 'Transform output');
        }
        return $results;
    }
}
