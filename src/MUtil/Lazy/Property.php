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
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Property extends \MUtil\Lazy\LazyAbstract
{
    private $_object;
    private $_property;

    public function __construct($object, $property)
    {
        $this->_object = $object;
        $this->_property = $property;
    }

    /**
    * The functions that fixes and returns a value.
    *
    * Be warned: this function may return a lazy value.
    *
    * @param \MUtil\Lazy\StackInterface $stack A \MUtil\Lazy\StackInterface object providing variable data
    * @return mixed
    */
    public function __toValue(\MUtil\Lazy\StackInterface $stack)
    {
        $object = $this->_object;
        while ($object instanceof \MUtil\Lazy\LazyInterface) {
            $object = $object->__toValue($stack);
        }

        $property = $this->_property;
        while ($property instanceof \MUtil\Lazy\LazyInterface) {
            $property = $property->__toValue($stack);
        }

        if (is_object($object)) {
            if (isset($object->$property)) {
                return $object->$property;
            } /* else {
               \MUtil\EchoOut\EchoOut::r(get_class($object), 'NO PROPERTY ' . $property);
            } // */
        }  /* else {
            \MUtil\EchoOut\EchoOut::r($object, 'NO OBJECT ' . $property);
        } // */
    }
}
