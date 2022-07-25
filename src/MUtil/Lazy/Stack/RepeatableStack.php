<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Lazy_Stack
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Lazy\Stack;

/**
 *
 *
 * @package    MUtil
 * @subpackage Lazy_Stack
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.4 17-jan-2015 14:30:59
 */
class RepeatableStack implements \MUtil\Lazy\StackInterface
{
    /**
     * The object to get properties from
     *
     * @var \MUtil\Lazy\Repeatable
     */
    protected $_object;

    /**
     *
     * @param \MUtil\Lazy\Repeatable $object
     */
    public function __construct(\MUtil\Lazy\Repeatable $object)
    {
        $this->_object = $object;
    }

    /**
     * Returns a value for $name
     *
     * @param string $name A name indentifying a value in this stack.
     * @return A value for $name
     */
    public function lazyGet($name)
    {
        // \MUtil\EchoOut\EchoOut::track($name, isset($this->_object->$name), \MUtil\Lazy::rise($this->_object->$name), $this->_object->getLazyValue($name));
        $value = $this->_object->__get($name);
        if ($value instanceof \MUtil\Lazy\LazyInterface) {
            return \MUtil\Lazy::rise($value);
        }
        return $value;
    }

    /**
     * Set this stack to throw an exception
     *
     * @param mixed $throw boolean
     * @return \MUtil_ArrayStack (continuation pattern_
     */
    public function setThrowOnMiss($throw = true)
    {
        $this->_throwOnMiss = $throw;
        return $this;
    }
}
