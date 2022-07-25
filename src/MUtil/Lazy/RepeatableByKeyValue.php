<?php

/**
 *
 * @package    MUtil
 * @subpackage Lazy
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Lazy;

/**
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class RepeatableByKeyValue extends \MUtil\Lazy\Repeatable
{
    /**
     *
     * @param array|Traversable $data
     * @throws \MUtil\Lazy\LazyException
     */
    public function __construct($data)
    {
        if (! (is_array($data) || ($data instanceof \Traversable))) {
            throw new \MUtil\Lazy\LazyException('The $data parameter is not an array or a \Traversable interface instance ');
        }

        $result = array();

        foreach($data as $key => $value) {
            $result[] = array('key' => $key, 'value' => $value);
        }

        parent::__construct($result);
    }
}