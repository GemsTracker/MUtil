<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Lazy
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Lazy;

/**
 * Wrap lazyness around an array.
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
class ArrayWrap extends \MUtil\Lazy\ObjectWrap
{
    public function __construct(array $array = array())
    {
        parent::__construct(new \ArrayObject($array));
    }

    public function offsetExists($offset)
    {
        return $this->_object->offsetExists($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->_object->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->_object->offsetUnset($offset);
    }
}
