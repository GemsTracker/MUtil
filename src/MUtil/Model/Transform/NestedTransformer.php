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
 * Transform that can be used to put nested submodels in a model
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.6.2
 */
class NestedTransformer extends \MUtil\Model\SubmodelTransformerAbstract
{
    /**
     * Set to true when a submodel should not be saved
     *
     * @var boolean
     */
    public $skipSave = false;

    /**
     * If the transformer add's fields, these should be returned here.
     * Called in $model->AddTransformer(), so the transformer MUST
     * know which fields to add by then (optionally using the model
     * for that).
     *
     * @param \MUtil\Model\ModelAbstract $model The parent model
     * @return array Of filedname => set() values
     */
    public function getFieldInfo(MetaModelInterface $model)
    {
        $data = array();
        foreach ($this->_subModels as $sub) {
            foreach ($sub->getItemNames() as $name) {
                if (! $model->has($name)) {
                    $data[$name] = $sub->get($name);
                    $data[$name]['no_text_search'] = true;

                    // Remove unsuited data
                    unset($data[$name]['table'], $data[$name]['column_expression']);
                    unset($data[$name]['label']);
                    $data[$name]['elementClass'] = 'None';

                    // Remove the submodel's own transformers to prevent changed/created to show up in the data array instead of only in the nested info
                    unset($data[$name][\MUtil\Model\ModelAbstract::LOAD_TRANSFORMER]);
                    unset($data[$name][\MUtil\Model\ModelAbstract::SAVE_TRANSFORMER]);
                }
            }
        }
        return $data;
    }

    /**
     * Function to allow overruling of transform for certain models
     *
     * @param \MUtil\Model\ModelAbstract $model Parent model
     * @param \MUtil\Model\ModelAbstract $sub Sub model
     * @param array $data The nested data rows
     * @param array $join The join array
     * @param string $name Name of sub model
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     */
    protected function transformLoadSubModel(
            \MUtil\Model\ModelAbstract $model, \MUtil\Model\ModelAbstract $sub, array &$data, array $join,
            $name, $new, $isPostData)
    {
        foreach ($data as $key => $row) {
            // E.g. if loaded from a post
            if (isset($row[$name])) {
                $rows = $sub->processAfterLoad($row[$name], $new, $isPostData);
            } elseif ($new) {
                $rows = $sub->loadAllNew();
            } else {
                $filter = $sub->getFilter();

                foreach ($join as $parent => $child) {
                    if (isset($row[$parent])) {
                        $filter[$child] = $row[$parent];
                    }
                }
                // If $filter is empty, treat as new
                if (empty($filter)) {
                    $rows = $sub->loadAllNew();
                } else {
                    $rows = $sub->load($filter);
                }
            }

            $data[$key][$name] = $rows;
        }
    }

    /**
     * Function to allow overruling of transform for certain models
     *
     * @param \MUtil\Model\ModelAbstract $model
     * @param \MUtil\Model\ModelAbstract $sub
     * @param array $data
     * @param array $join
     * @param string $name
     */
    protected function transformSaveSubModel(
            \MUtil\Model\ModelAbstract $model, \MUtil\Model\ModelAbstract $sub, array &$row, array $join, $name)
    {
        if ($this->skipSave) {
            return;
        }

        if (! isset($row[$name])) {
            return;
        }

        $data = $row[$name];
        $keys = array();

        // Get the parent key values.
        foreach ($join as $parent => $child) {
            if (isset($row[$parent])) {
                $keys[$child] = $row[$parent];
            } else {
                // if there is no parent identifier set, don't save
                return;
            }
        }
        foreach($data as $key => $subrow) {
            // Make sure the (possibly changed) parent key
            // is stored in the sub data.
            $data[$key] = $keys + $subrow;
        }

        $saved = $sub->saveAll($data);

        $row[$name] = $saved;
    }

    /**
     * This transform function checks the sort to
     * a) remove sorts from the main model that are not possible
     * b) add sorts that are required needed
     *
     * @param \MUtil\Model\ModelAbstract $model
     * @param array $sort
     * @return array The (optionally changed) sort
     */
    public function transformSort(MetaModelInterface $model, array $sort)
    {
        foreach ($this->_subModels as $sub) {
            foreach ($sort as $key => $value) {
                if ($sub->has($key)) {
                    // Make sure the filter is applied during load
                    $sub->addSort(array($key => $value));

                    // Remove all sorts on columns from the submodel
                    unset($sort[$key]);
                }
            }
        }

        return $sort;
    }
}
