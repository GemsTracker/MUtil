<?php

/**
 *
 * @package    MUtil
 * @subpackage Lazy
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

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
class MUtil_Lazy
{
    /**
     * The default stack to use
     *
     * @var \MUtil_Lazy_StackInterface
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
     * @return \MUtil_Lazy_Alternate
     */
    public static function alternate($value1, $value2)
    {
        $args = func_get_args();
        return new \MUtil_Lazy_Alternate($args);
    }

    /**
     * Execute this call later
     *
     * @param callable $callable
     * @param mixed $arg_array All other arguments are used to call the function at a later time
     * @return \MUtil_Lazy_Call
     */
    public static function call($callable, $arg_array = null)
    {
        $args = array_slice(func_get_args(), 1);
        return new \MUtil_Lazy_Call($callable, $args);
    }

    /**
     * Create a lazy comparison operation
     *
     * @param mixed $opLeft
     * @param string $oper The operator to use for this comparison
     * @param mixed $opRight
     * @return \MUtil_Lazy_Call
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
        return new \MUtil_Lazy_Call($lambda, array($opLeft, $opRight));
    }

    /**
     * The arguments are flattened lazily into one single array
     * and then joined together without separator.
     *
     * @param mixed $arg_array
     * @return \MUtil_Lazy_Call
     */
    public static function concat($arg_array = null)
    {
        $args = func_get_args();

        return new \MUtil_Lazy_Call('implode', array('', new \MUtil_Lazy_Call('MUtil_Ra::flatten', array($args))));
    }

    public static function first($args_array)
    {
        $args = func_get_args();

        // Last value first
        $result = array_shift($args);

        foreach ($args as $arg) {
            $result = new \MUtil_Lazy_Call(array($result, 'if'), array($result, $arg));
        }
        return $result;
    }

    /**
     * Lazy if statement
     *
     * @param mixed $if The value tested during raise
     * @param mixed $then The value after raise when $if is true
     * @param mixed $else The value after raise when $if is false
     * @return \MUtil_Lazy_Call
     */
    public static function iff($if, $then, $else = null)
    {
        return new \MUtil_Lazy_Call(array($if, 'if'), array($then, $else));
    }

    /**
     * Lazy if statement
     *
     * @param mixed $if The value tested during raise
     * @param mixed $then The value after raise when $if is true
     * @param mixed $else The value after raise when $if is false
     * @return \MUtil_Lazy_Call
     */
    public static function iif($if, $then, $else = null)
    {
        return new \MUtil_Lazy_Call(array($if, 'if'), array($then, $else));
    }

    /**
     * Returns a Lazy version of the parameter
     *
     * @param mixed $var
     * @return \MUtil_Lazy_LazyInterface
     */
    public static function L($var)
    {
        if (is_object($var)) {
            if ($var instanceof \MUtil_Lazy_LazyInterface) {
                return $var;
            } elseif ($var instanceof \MUtil_Lazy_Procrastinator) {
                return $var->toLazy();
            }

            return new \MUtil_Lazy_ObjectWrap($var);

        } elseif(is_array($var)) {
            return new \MUtil_Lazy_ArrayWrap($var);

        } else {
            return new \MUtil_Lazy_LazyGet($var);
        }
    }

    /**
     * Return a lazy callable to an object
     *
     * @param Object $object
     * @param string $method Method of the object
     * @param mixed $arg_array1 Optional, first of any arguments to the call
     * @return \MUtil_Lazy_Call
     */
    public static function method($object, $method, $arg_array1 = null)
    {
        $args = array_slice(func_get_args(), 2);
        return new \MUtil_Lazy_Call(array($object, $method), $args);
    }

    /**
     * Get a named call to the lazy stack
     *
     * @return \MUtil_Lazy_LazyGet
     */
    public static function get($name)
    {
        return new \MUtil_Lazy_LazyGet($name);
    }

    /**
     * Get the current stack or none
     *
     * @return \MUtil_Lazy_StackInterface
     */
    public static function getStack()
    {
        if (! self::$_stack instanceof \MUtil_Lazy_StackInterface) {
            self::$_stack = new \MUtil_Lazy_Stack_EmptyStack(__CLASS__);
        }

        return self::$_stack;
    }

    /**
     * Perform a lazy call to an array
     *
     * @param mixed $array
     * @param mixed $offset
     * @return \MUtil_Lazy_ArrayAccessor
     */
    public static function offsetGet($array, $offset)
    {
        return new \MUtil_Lazy_ArrayAccessor($array, $offset);
    }

    /**
     * Return a lazy retrieval of an object property
     *
     * @param Object $object
     * @param string $property Property of the object
     * @return \MUtil_Lazy_Property
     */
    public static function property($object, $property)
    {
        return new \MUtil_Lazy_Property($object, $property);
    }

    /**
     * Raises a \MUtil_Lazy_LazyInterface one level, but may still
     * return a \MUtil_Lazy_LazyInterface.
     *
     * This function is usually used to perform a e.g. filter function on object that may e.g.
     * contain Repeater objects.
     *
     * @param mixed $object Usually an object of type \MUtil_Lazy_LazyInterface
     * @param mixed $stack Optional variable stack for evaluation
     * @return mixed
     */
    public static function raise($object, $stack = null)
    {
        //\MUtil_Echo::countOccurences(__FUNCTION__);
        if ($object instanceof \MUtil_Lazy_LazyInterface) {
            if (! $stack instanceof \MUtil_Lazy_StackInterface) {
                if (self::$_stack instanceof \MUtil_Lazy_StackInterface) {
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
     * @return \MUtil_Lazy_RepeatableInterface
     */
    public static function repeat($repeatable)
    {
        if ($repeatable instanceof \MUtil_Lazy_RepeatableInterface) {
            return $repeatable;
        }

        return new \MUtil_Lazy_Repeatable($repeatable);
    }

    /**
     * Raises a \MUtil_Lazy_LazyInterface until the return object is not a
     * \MUtil_Lazy_LazyInterface object.
     *
     * @param mixed $object Usually an object of type \MUtil_Lazy_LazyInterface
     * @param mixed $stack Optional variable stack for evaluation
     * @return mixed Something not lazy
     */
    public static function rise($object, $stack = null)
    {
        // \MUtil_Echo::countOccurences(__FUNCTION__);
        // \MUtil_Echo::timeFunctionStart(__FUNCTION__);
        if ($object instanceof \MUtil_Lazy_LazyInterface || is_array($object)) {
            if (! $stack instanceof \MUtil_Lazy_StackInterface) {
                if (self::$_stack instanceof \MUtil_Lazy_StackInterface) {
                    $stack = self::$_stack;
                } else {
                    $stack = self::getStack();
                }
            }
            if (is_array($object)) {
                $object = self::riseRa($object, $stack);
            } else {
                while ($object instanceof \MUtil_Lazy_LazyInterface) {
                    $object = $object->__toValue($stack);
                }
            }
        }
        // \MUtil_Echo::timeFunctionStop(__FUNCTION__);
        return $object;
    }

    public static function riseObject(\MUtil_Lazy_LazyInterface $object, \MUtil_Lazy_StackInterface $stack)
    {
        while ($object instanceof \MUtil_Lazy_LazyInterface) {
            $object = $object->__toValue($stack);
        }

        if ($object && is_array($object)) {
            return self::riseRa($object, $stack);
        }

        return $object;
    }

    public static function riseRa(array $object, \MUtil_Lazy_StackInterface $stack)
    {
        foreach ($object as $key => &$val) {
            while ($val instanceof \MUtil_Lazy_LazyInterface) {
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
     * @return \MUtil_Lazy_StackInterface
     */
    public static function setStack($stack)
    {
        if ($stack instanceof \MUtil_Lazy_StackInterface) {
            self::$_stack = $stack;

        } elseif ($stack instanceof \MUtil_Model_Bridge_TableBridgeAbstract) {
            self::$_stack = new \MUtil_Lazy_Stack_BridgeStack($stack);

        } elseif ($stack instanceof \MUtil_Lazy_Repeatable) {
            self::$_stack = new RepeatableStack($stack);

        } elseif (\MUtil_Ra::is($stack)) {
            $stack = \MUtil_Ra::to($stack);

            self::$_stack = new \MUtil_Lazy_Stack_ArrayStack($stack);

        } elseif (is_object($stack)) {
            self::$_stack = new \MUtil_Lazy_Stack_ObjectStack($stack);

        } else {
            throw new \MUtil_Lazy_LazyException("Lazy stack set to invalid scalar type.");
        }

        return self::$_stack;
    }
}
