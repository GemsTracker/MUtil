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
interface LazyInterface extends \ArrayAccess
{
    /**
     * Return a lazy version of the call
     *
     * @return \MUtil\Lazy\LazyInterface
     */
    public function __call($name, array $arguments);

    /**
     * Return a lazy version of the property retrieval
     *
     * @return \MUtil\Lazy\LazyInterface
     */
    public function __get($name);

    /**
     * Every Lazy Interface implementation has to try to
     * change the result to a string or return an error
     * message as a string.
     *
     * @return string
     */
    public function __toString();

    /**
     * The functions that fixes and returns a value.
     *
     * Be warned: this function may return a lazy value.
     *
     * @param \MUtil\Lazy\StackInterface $stack A \MUtil\Lazy\StackInterface object providing variable data
     * @return mixed
     */
     public function __toValue(\MUtil\Lazy\StackInterface $stack);

    // public function offsetExists($offset);
    // public function offsetGet($offset);
    // public function offsetSet($offset, $value);
    // public function offsetUnset($offset);
}