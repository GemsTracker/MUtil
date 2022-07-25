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
class BridgeStack implements \MUtil\Lazy\StackInterface
{
    /**
     * The object to get properties from
     *
     * @var \MUtil\Model\Bridge\TableBridgeAbstract
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
    public function __construct(\MUtil\Model\Bridge\TableBridgeAbstract $object)
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
        return $this->_object->getLazyValue($name);
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
