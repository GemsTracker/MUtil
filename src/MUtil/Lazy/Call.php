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
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Call extends \MUtil\Lazy\LazyAbstract
{
    private $_callable;
    private $_params;

    public function __construct($callable, array $params = array())
    {
        $this->_callable = $callable;
        $this->_params   = $params;
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
        $params = $this->_params;

        if (is_array($this->_callable)) {
            list($object, $method) = $this->_callable;
            while ($object instanceof \MUtil\Lazy\LazyInterface) {
                $object = $object->__toValue($stack);
            }
            $callable = array($object, $method);

            if (! (is_object($object) && (method_exists($object, $method) || method_exists($object, '__call')))) {
                if (function_exists($method)) {
                    // Add the object as the first parameter
                    array_unshift($params, $object);
                    $callable = $method;

                } elseif ('if' === strtolower($method)) {
                    if ($object) {
                        return isset($params[0]) ? $params[0] : null;
                    } else {
                        return isset($params[1]) ? $params[1] : null;
                    }
                }
            }

        } else {
            $method   = $this->_callable; // For error message
            $callable = $this->_callable;
        }

        if (is_callable($callable)) {
            $params = \MUtil\Lazy::riseRa($params, $stack);
            /* if ('_' == $method) {
                \MUtil\EchoOut\EchoOut::r($params);
            } */
            
            return call_user_func_array($callable, $params);
        }

        throw new \MUtil\Lazy\LazyException('Lazy execution exception! "' . $method . '" is not a valid callable.');
    }
}
