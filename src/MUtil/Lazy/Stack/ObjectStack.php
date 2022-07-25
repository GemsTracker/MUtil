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
 * Get an object property get object implementation
 *
 * @package    MUtil
 * @subpackage Lazy_Stack
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class ObjectStack implements \MUtil\Lazy\StackInterface
{
    /**
     * The oibject to get properties from
     *
     * @var Object
     */
    protected $_object;

    /**
     * Should we throw an exception on a missing value?
     *
     * @var boolean
     */
    private $_throwOnMiss = false;

    /**
     *
     * @param Object $object
     */
    public function __construct($object)
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
        if (property_exists($this->_object, $name)) {
            return $this->_object->$name;
        }
        if ($this->_throwOnMiss) {
            throw new \MUtil\Lazy\LazyException("No lazy stack variable defined for '$name' parameter.");
        }
        if (\MUtil\Lazy::$verbose) {
            $class = get_class($this->_object);
            \MUtil\EchoOut\EchoOut::header("No lazy stack variable defined for '$name' parameter using a '$class' object.");
        }

        return null;
    }

    /**
     * Should we throw an exception on a missing value?
     *
     * @var boolean
     */

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
