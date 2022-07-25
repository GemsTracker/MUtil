<?php

/**
 *
 * @package    MUtil
 * @subpackage Ra
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @author     Matijs de Jong
 */

namespace MUtil\Ra;

/**
 * The Ra_Nested class contains static array processing functions that are used on nested arrays.
 *
 * Ra class: pronouce "array" except on 19 september, then it is "ahrrray".
 *
 * The functions are:
 *  \MUtil\Ra\Nested::toTree => Creates a tree array
 *
 * @package    MUtil
 * @subpackage Ra
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class Nested
{
    /**
     *
     * <code>
       $select = $db->select();
       $select->from('gems__rounds', array('gro_id_track', 'gro_id_survey', 'gro_id_round', 'gro_id_order'))->where('gro_id_track = 220');
       $existing = $select->query()->fetchAll();
       \MUtil\EchoOut\EchoOut::r(\MUtil\Ra\Nested::toTree($existing), 'Auto tree');
       \MUtil\EchoOut\EchoOut::r(\MUtil\Ra\Nested::toTree($existing, 'gro_id_track', 'gro_id_survey'), 'Named tree with set at end (data loss in this case)');
       \MUtil\EchoOut\EchoOut::r(\MUtil\Ra\Nested::toTree($existing, 'gro_id_track', 'gro_id_survey', null), 'Named tree with append');
       \MUtil\EchoOut\EchoOut::r(\MUtil\Ra\Nested::toTree($existing, 'gro_id_track', null, 'gro_id_survey', null), 'Named tree with double append');
     </code>
     */
    public static function toTree(array $data, $key_args = null)
    {
        if (! $data) {
            return $data;
        }

        if (func_num_args() == 1) {
            // Get the keys of the first nested item
            $keys = array_keys(reset($data));
        } else {
            $keys = \MUtil\Ra::args(func_get_args(), 1);
        }

        $valueKeys = array_diff(array_keys(reset($data)), $keys);

        switch (count($valueKeys)) {
            case 0:
                // Drop the last item
                $valueKey = array_pop($keys);
                $valueKeys = false;
                break;

            case 1:
                $valueKey = reset($valueKeys);
                $valueKeys = false;
                break;

        }

        $results = array();
        foreach ($data as $item) {
            $current =& $results;
            foreach ($keys as $key) {
                if (null === $key) {
                    $count = count($current);

                    $current[$count] = array();
                    $current =& $current[$count];

                } elseif (array_key_exists($key, $item)) {
                    $value = $item[$key];

                    if (! array_key_exists($value, $current)) {
                        $current[$value] = array();
                    }

                    $current =& $current[$value];
                }
            }
            if ($valueKeys) {
                foreach ($valueKeys as $key) {
                    $current[$key] = $item[$key];
                }
            } else {
                $current = $item[$valueKey];
            }
        }
        return $results;
    }
}
