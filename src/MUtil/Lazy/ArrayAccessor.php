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
 * A lazy object for when you want to access an array but either the array
 * itself and/or the offset is a lazy object.
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */

class ArrayAccessor extends \MUtil\Lazy\LazyAbstract
{
    /**
     *
     * @var mixed Possibly lazy array
     */
    private $_array;

    /**
     *
     * @var mixed Possibly lazy offset
     */
    private $_offset;

    /**
     *
     * @param mixed Possibly lazy array
     * @param mixed Possibly lazy offset
     */
    public function __construct($array, $offset)
    {
        $this->_array  = $array;
        $this->_offset = $offset;
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
        $array  = $this->_array;
        $offset = $this->_offset;

        while ($offset instanceof \MUtil\Lazy\LazyInterface) {
            $offset = $offset->__toValue($stack);
        }
        while ($array instanceof \MUtil\Lazy\LazyInterface) {
            $array = $array->__toValue($stack);
        }

        if (\MUtil\Lazy::$verbose) {
            \MUtil\EchoOut\EchoOut::header('Lazy offset get for offset: <em>' . $offset . '</em>');
            \MUtil\EchoOut\EchoOut::classToName($array);
        }

        if (null === $offset) {
            if (isset($array[''])) {
                $value = $array[''];
            } else {
                $value = null;
            }
        } elseif (is_array($offset)) {
            // When the offset is itself an array, return an
            // array of values applied to this offset.
            $value = array();
            foreach (\MUtil\Lazy::riseRa($offset, $stack) as $key => $val) {
                if (isset($array[$val])) {
                    $value[$key] = $val;
                }
            }
        } elseif (isset($array[$offset])) {
            $value = $array[$offset];
        } else {
            $value = null;
        }

        while ($value instanceof \MUtil\Lazy\LazyInterface) {
            $value = $value->__toValue($stack);
        }
        if (is_array($value)) {
            $value = \MUtil\Lazy::riseRa($value, $stack);
        }
        return $value;
    }

}
