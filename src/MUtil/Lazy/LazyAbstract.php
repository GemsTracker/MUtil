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
 * The basic workhorse function for all Lazy objects.
 *
 * It returns a new Lazy object for every call, property get or array offsetGet
 * applied to the sub class Lazy object and implements the Lazy interface
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
abstract class LazyAbstract implements \MUtil\Lazy\LazyInterface
{
    /**
     * Return a lazy version of the call
     *
     * @return \MUtil\Lazy\Call
     */
    public function __call($name, array $arguments)
    {
        return new \MUtil\Lazy\Call(array($this, $name), $arguments);
    }

    /**
     * Return a lazy version of the property retrieval
     *
     * @return \MUtil\Lazy\Property
     */
    public function __get($name)
    {
        // WARNING
        //
        // I thought about caching properties. Always useful when a property is
        // used a lot. However, this would mean that every LazyAbstract value
        // would have to store a cache, just in case this happens.
        //
        // All in all I concluded the overhead is probably not worth it, though I
        // did not test this.
        return new \MUtil\Lazy\Property($this, $name);
    }

    /**
     * You cannot set a Lazy object.
     *
     * throws \MUtil\Lazy\LazyException
     */
    public function __set($name, $value)
    {
        throw new \MUtil\Lazy\LazyException('You cannot set a Lazy object.');
    }

    /**
     * Every Lazy Interface implementation has to try to
     * change the result to a string or return an error
     * message as a string.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $stack = new \MUtil\Lazy\Stack\EmptyStack(__FUNCTION__);
            $value = $this;

            while ($value instanceof \MUtil\Lazy\LazyInterface) {
                $value = $this->__toValue($stack);
            }

            if (is_string($value)) {
                return $value;
            }

            // TODO: test
            if (is_object($value) && (! method_exists($value, '__toString'))) {
                return 'Object of type ' . get_class($value) . ' cannot be converted to string value.';
            }

            return (string) $value;

        } catch (\Exception $e) {
            // Cannot pass exception from __toString().
            //
            // So catch all exceptions and return error message.
            // Make sure to use @see get() if you do not want this to happen.
            return $e->getMessage();
        }
    }

    /**
     * Returns a lazy call where this object is the first parameter
     *
     * @param $callableOrObject object|callable
     * @param $nameOrArg1 optional method|mixed
     * @param $argn optional mixed
     * @return LazyInterface
     */
    public function call($callableOrObject, $nameOrArg1 = null, $argn = null)
    {
        $args = func_get_args();
        $callable = array_shift($args);

        if (is_callable($callable)) {
            // Put $this as the first parameter
            array_unshift($args, $this);

        } elseif (is_object($callable)) {
            // Second argument should be string that is function name
            $callable = array($callable, array_shift($args));

            // Put $this as the first parameter
            array_unshift($args, $this);

        } else {
            // First argument should be method of this object.
            $callable = array($this, $callable);
        }

        return new \MUtil\Lazy\Call($callable, $args);
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return new \MUtil\Lazy\ArrayAccessor($this, $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \MUtil\Lazy\LazyException('You cannot set a Lazy object.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \MUtil\Lazy\LazyException('You cannot unset a Lazy object.');
    }
}
