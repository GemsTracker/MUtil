<?php

/**
 *
 * @package    MUtil
 * @subpackage Lazy
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil;

use MUtil\Lazy\Stack\RepeatableStack;

/**
 * Why get lazy:
 * 1 - You want to use a result later that is not yet known
 * 2 - You want the result repeated for a sequence of items
 * 3 - You want a result on some object but do not have the object yet
 *
 * What is a result you might want:
 * 1 - the object itself
 * 2 - a call to an object method
 * 3 - an object propery
 * 4 - an array object
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Lazy
{
    /**
     * The default stack to use
     *
     * @var \MUtil\Lazy\StackInterface
     */
    private static $_stack;

    /**
     * Static variable for debuggging purposes. Toggles the echoing of e.g. raised results.
     *
     * @var boolean When true Lazy objects should start outputting what is happening in them.
     */
    public static $verbose = false;

    /**
     * Returns a lazy object that alternates through all the parameters used
     * to call this function. (At least two , but more is allowed.)
     *
     * @param mixed $value1
     * @param mixed $value2
     * @return \MUtil\Lazy\Alternate
     */
    public static function alternate($value1, $value2)
    {
        $args = func_get_args();
        return new \MUtil\Lazy\Alternate($args);
    }

    /**
     * Execute this call later
     *
     * @param callable $callable
     * @param mixed $arg_array All other arguments are used to call the function at a later time
     * @return \MUtil\Lazy\Call
     */
    public static function call($callable, $arg_array = null)
    {
        $args = array_slice(func_get_args(), 1);
        return new \MUtil\Lazy\Call($callable, $args);
    }

    /**
     * Create a lazy comparison operation
     *
     * @param mixed $opLeft
     * @param string $oper The operator to use for this comparison
     * @param mixed $opRight
     * @return \MUtil\Lazy\Call
     */
    public static function comp($opLeft, $oper, $opRight)
    {
        switch ($oper) {
            case '==':
                $lambda = function ($a, $b) {return $a == $b;};
                break;

            case '===':
                $lambda = function ($a, $b) {return $a === $b;};
                break;

            case '!=':
            case '<>':
                $lambda = function ($a, $b) {return $a <> $b;};
                break;

            case '!==':
                $lambda = function ($a, $b) {return $a !== $b;};
                break;

            case '<':
                $lambda = function ($a, $b) {return $a < $b;};
                break;

            case '<=':
                $lambda = function ($a, $b) {return $a <= $b;};
                break;

            case '>':
                $lambda = function ($a, $b) {return $a > $b;};
                break;

            case '>=':
                $lambda = function ($a, $b) {return $a >= $b;};
                break;

//            case '<=>':
//                $lambda = function ($a, $b) {return $a <=> $b;};
//                break;

            default:
                $lambda = function ($a, $b) use ($oper) {
                    return eval('return $a ' . $oper . ' $b;');
                };
                break;
        }
        return new \MUtil\Lazy\Call($lambda, array($opLeft, $opRight));
    }

    /**
     * The arguments are flattened lazily into one single array
     * and then joined together without separator.
     *
     * @param mixed $arg_array
     * @return \MUtil\Lazy\Call
     */
    public static function concat($arg_array = null)
    {
        $args = func_get_args();

        return new \MUtil\Lazy\Call('implode', array('', new \MUtil\Lazy\Call('\\MUtil\\Ra::flatten', array($args))));
    }

    public static function first($args_array)
    {
        $args = func_get_args();

        // Last value first
        $result = array_shift($args);

        foreach ($args as $arg) {
            $result = new \MUtil\Lazy\Call(array($result, 'if'), array($result, $arg));
        }
        return $result;
    }

    /**
     * Lazy if statement
     *
     * @param mixed $if The value tested during raise
     * @param mixed $then The value after raise when $if is true
     * @param mixed $else The value after raise when $if is false
     * @return \MUtil\Lazy\Call
     */
    public static function iff($if, $then, $else = null)
    {
        return new \MUtil\Lazy\Call(array($if, 'if'), array($then, $else));
    }

    /**
     * Lazy if statement
     *
     * @param mixed $if The value tested during raise
     * @param mixed $then The value after raise when $if is true
     * @param mixed $else The value after raise when $if is false
     * @return \MUtil\Lazy\Call
     */
    public static function iif($if, $then, $else = null)
    {
        return new \MUtil\Lazy\Call(array($if, 'if'), array($then, $else));
    }

    /**
     * Returns a Lazy version of the parameter
     *
     * @param mixed $var
     * @return \MUtil\Lazy\LazyInterface
     */
    public static function L($var)
    {
        if (is_object($var)) {
            if ($var instanceof \MUtil\Lazy\LazyInterface) {
                return $var;
            } elseif ($var instanceof \MUtil\Lazy\Procrastinator) {
                return $var->toLazy();
            }

            return new \MUtil\Lazy\ObjectWrap($var);

        } elseif(is_array($var)) {
            return new \MUtil\Lazy\ArrayWrap($var);

        } else {
            return new \MUtil\Lazy\LazyGet($var);
        }
    }

    /**
     * Return a lazy callable to an object
     *
     * @param Object $object
     * @param string $method Method of the object
     * @param mixed $arg_array1 Optional, first of any arguments to the call
     * @return \MUtil\Lazy\Call
     */
    public static function method($object, $method, $arg_array1 = null)
    {
        $args = array_slice(func_get_args(), 2);
        return new \MUtil\Lazy\Call(array($object, $method), $args);
    }

    /**
     * Get a named call to the lazy stack
     *
     * @return \MUtil\Lazy\LazyGet
     */
    public static function get($name)
    {
        return new \MUtil\Lazy\LazyGet($name);
    }

    /**
     * Get the current stack or none
     *
     * @return \MUtil\Lazy\StackInterface
     */
    public static function getStack()
    {
        if (! self::$_stack instanceof \MUtil\Lazy\StackInterface) {
            self::$_stack = new \MUtil\Lazy\Stack\EmptyStack(__CLASS__);
        }

        return self::$_stack;
    }

    /**
     * Perform a lazy call to an array
     *
     * @param mixed $array
     * @param mixed $offset
     * @return \MUtil\Lazy\ArrayAccessor
     */
    public static function offsetGet($array, $offset)
    {
        return new \MUtil\Lazy\ArrayAccessor($array, $offset);
    }

    /**
     * Return a lazy retrieval of an object property
     *
     * @param Object $object
     * @param string $property Property of the object
     * @return \MUtil\Lazy\Property
     */
    public static function property($object, $property)
    {
        return new \MUtil\Lazy\Property($object, $property);
    }

    /**
     * Raises a \MUtil\Lazy\LazyInterface one level, but may still
     * return a \MUtil\Lazy\LazyInterface.
     *
     * This function is usually used to perform a e.g. filter function on object that may e.g.
     * contain Repeater objects.
     *
     * @param mixed $object Usually an object of type \MUtil\Lazy\LazyInterface
     * @param mixed $stack Optional variable stack for evaluation
     * @return mixed
     */
    public static function raise($object, $stack = null)
    {
        //\MUtil\EchoOut\EchoOut::countOccurences(__FUNCTION__);
        if ($object instanceof \MUtil\Lazy\LazyInterface) {
            if (! $stack instanceof \MUtil\Lazy\StackInterface) {
                if (self::$_stack instanceof \MUtil\Lazy\StackInterface) {
                    $stack = self::$_stack;
                } else {
                    $stack = self::getStack();
                }
            }
            return $object->__toValue($stack);
        } else {
            return $object;
        }
    }

    /**
     *
     * @param mixed $repeatable
     * @return \MUtil\Lazy\RepeatableInterface
     */
    public static function repeat($repeatable)
    {
        if ($repeatable instanceof \MUtil\Lazy\RepeatableInterface) {
            return $repeatable;
        }

        return new \MUtil\Lazy\Repeatable($repeatable);
    }

    /**
     * Raises a \MUtil\Lazy\LazyInterface until the return object is not a
     * \MUtil\Lazy\LazyInterface object.
     *
     * @param mixed $object Usually an object of type \MUtil\Lazy\LazyInterface
     * @param mixed $stack Optional variable stack for evaluation
     * @return mixed Something not lazy
     */
    public static function rise($object, $stack = null)
    {
        // \MUtil\EchoOut\EchoOut::countOccurences(__FUNCTION__);
        // \MUtil\EchoOut\EchoOut::timeFunctionStart(__FUNCTION__);
        if ($object instanceof \MUtil\Lazy\LazyInterface || is_array($object)) {
            if (! $stack instanceof \MUtil\Lazy\StackInterface) {
                if (self::$_stack instanceof \MUtil\Lazy\StackInterface) {
                    $stack = self::$_stack;
                } else {
                    $stack = self::getStack();
                }
            }
            if (is_array($object)) {
                $object = self::riseRa($object, $stack);
            } else {
                while ($object instanceof \MUtil\Lazy\LazyInterface) {
                    $object = $object->__toValue($stack);
                }
            }
        }
        // \MUtil\EchoOut\EchoOut::timeFunctionStop(__FUNCTION__);
        return $object;
    }

    public static function riseObject(\MUtil\Lazy\LazyInterface $object, \MUtil\Lazy\StackInterface $stack)
    {
        while ($object instanceof \MUtil\Lazy\LazyInterface) {
            $object = $object->__toValue($stack);
        }

        if ($object && is_array($object)) {
            return self::riseRa($object, $stack);
        }

        return $object;
    }

    public static function riseRa(array $object, \MUtil\Lazy\StackInterface $stack)
    {
        foreach ($object as $key => &$val) {
            while ($val instanceof \MUtil\Lazy\LazyInterface) {
                $val = $val->__toValue($stack);
            }
            if (is_array($val)) {
                $val = self::riseRa($val, $stack);
            }
        }

        return $object;
    }

    /**
     * Set the current stack
     *
     * @param mixed $stack Value to be turned into stack for evaluation
     * @return \MUtil\Lazy\StackInterface
     */
    public static function setStack($stack)
    {
        if ($stack instanceof \MUtil\Lazy\StackInterface) {
            self::$_stack = $stack;

        } elseif ($stack instanceof \MUtil\Model\Bridge\TableBridgeAbstract) {
            self::$_stack = new \MUtil\Lazy\Stack\BridgeStack($stack);

        } elseif ($stack instanceof \MUtil\Lazy\Repeatable) {
            self::$_stack = new RepeatableStack($stack);

        } elseif (\MUtil\Ra::is($stack)) {
            $stack = \MUtil\Ra::to($stack);

            self::$_stack = new \MUtil\Lazy\Stack\ArrayStack($stack);

        } elseif (is_object($stack)) {
            self::$_stack = new \MUtil\Lazy\Stack\ObjectStack($stack);

        } else {
            throw new \MUtil\Lazy\LazyException("Lazy stack set to invalid scalar type.");
        }

        return self::$_stack;
    }
}
