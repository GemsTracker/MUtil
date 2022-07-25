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

/**
 * Transform that can be used to join models to another model in possibly non-relational
 * ways.
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.2
 */
class JoinTransformer extends \MUtil\Model\SubmodelTransformerAbstract
{
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
        if (1 === count($join)) {
            // Suimple implementation
            $mkey = key($join);
            $skey = reset($join);

            $mfor = \MUtil\Ra::column($mkey, $data);

            // \MUtil\EchoOut\EchoOut::track($mfor);
            if ($new) {
                $sdata = $sub->loadNew(1);
            } else {
                $sdata = $sub->load(array($skey => $mfor));
            }
            // \MUtil\EchoOut\EchoOut::track($sdata);

            if ($sdata) {
                $skeys = array_flip(\MUtil\Ra::column($skey, $sdata));
                $empty = array_fill_keys(array_keys(reset($sdata)), null);

                foreach ($data as &$mrow) {
                    $mfind = $mrow[$mkey];

                    if (isset($skeys[$mfind])) {
                        $mrow += $sdata[$skeys[$mfind]];
                    } else {
                        $mrow += $empty;
                    }
                }
            } else {
                $empty = array_fill_keys($sub->getItemNames(), null);

                foreach ($data as &$mrow) {
                    $mrow += $empty;
                }
            }
            // \MUtil\EchoOut\EchoOut::track($mrow);
        } else {
            // Multi column implementation
            $empty = array_fill_keys($sub->getItemNames(), null);
            foreach ($data as &$mrow) {
                $filter = $sub->getFilter();
                foreach ($join as $from => $to) {
                    if (isset($mrow[$from])) {
                        $filter[$to] = $mrow[$from];
                    }
                }

                if ($new) {
                    $sdata = $sub->loadNew();
                } else {
                    $sdata = $sub->loadFirst($filter);
                }

                if ($sdata) {
                    $mrow += $sdata;
                } else {
                    $mrow += $empty;
                }

                // \MUtil\EchoOut\EchoOut::track($sdata, $mrow);
            }
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
    protected function transformSaveSubModel
            (\MUtil\Model\ModelAbstract $model, \MUtil\Model\ModelAbstract $sub, array &$row, array $join, $name)
    {
        $keys = array();

        // Get the parent key values.
        foreach ($join as $parent => $child) {
            if (isset($row[$parent])) {
                $keys[$child] = $row[$parent];
            }
        }

        $row   = $keys + $row;
        $saved = $sub->save($row);

        $row = $saved + $row;
    }
}
