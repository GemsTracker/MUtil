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
class ParallelRepeater implements \MUtil\Lazy\RepeatableInterface
{
    protected $repeatables = array();

    public function __construct($repeatable_args = null)
    {
        $args = \MUtil\Ra::args(func_get_args());
        foreach ($args as $id => $repeatable) {
            if (null != $repeatable) {
                $this->addRepeater($repeatable, $id);
            }
        }
    }

    /**
     * Returns the current item. Starts the loop when needed.
     *
     * return mixed The current item
     */
    public function __current()
    {
        $results = array();
        foreach ($this->repeatables as $id => $repeater) {
            if ($result = $repeater->__curent()) {
                $results[$id] = $result;
            }
        }
        return $results;
    }

    /**
     * Return a lazy version of the property retrieval
     *
     * @return \MUtil\Lazy\LazyInterface
     */
    public function __get($name)
    {
        $results = array();
        foreach ($this->repeatables as $id => $repeater) {
            if ($result = $repeater->$name) {
                $results[$id] = $result;
            }
        }
        return $results;
    }

    /**
     * Return the core data in the Repeatable in one go
     *
     * @return \Iterator|array
     */
    public function __getRepeatable()
    {
        $results = array();
        foreach ($this->repeatables as $id => $repeater) {
            if ($result = $repeater->__getRepeatable()) {
                $results[$id] = $result;
            }
        }
        return $results;
    }

    /**
     * Returns the current item. Starts the loop when needed.
     *
     * return mixed The current item
     */
    public function __next()
    {
        $results = array();
        foreach ($this->repeatables as $id => $repeater) {
            if ($result = $repeater->__next()) {
                $results[$id] = $result;
            }
        }
        // \MUtil\EchoOut\EchoOut::r($results, 'Parallel next');
        return $results;
    }

    /**
     * Return a lazy version of the property retrieval
     *
     * @return \MUtil\Lazy\Property
     */
    public function __set($name, $value)
    {
        throw new \MUtil\Lazy\LazyException('You cannot set a Lazy object.');
    }

    /**
     * The functions that starts the loop from the beginning
     *
     * @return mixed True if there is data.
     */
    public function __start()
    {
        $result = false;
        foreach ($this->repeatables as $repeater) {
            $result = $repeater->__start() || $result;
        }
        // \MUtil\EchoOut\EchoOut::r(array_keys($this->repeatables), 'Parallel start');
        // \MUtil\EchoOut\EchoOut::r($result, 'Parallel start');
        return $result;
    }

    public function addRepeater($repeater, $id = null)
    {
        if (! $repeater instanceof \MUtil\Lazy\RepeatableInterface) {
            $repeater = new \MUtil\Lazy\Repeatable($repeater);
        }
        if (null === $id) {
            $this->repeatables[] = $repeater;
        } else {
            $this->repeatables[$id] = $repeater;
        }

        return $repeater;
    }

    public function offsetExists($offset)
    {
        foreach ($this->repeatables as $repeater) {
            if ($repeater->offsetExists($offset)) {
                return true;
            }
        }

        return false;
    }

    public function offsetGet($offset)
    {
        $results = array();
        foreach ($this->repeatables as $id => $repeater) {
            if ($result = $repeater[$offset]) {
                $results[$id] = $result;
            }
        }
        return $results;
    }

    public function offsetSet($offset, $value)
    {
        throw new \MUtil\Lazy\LazyException('You cannot set a Lazy object.');
    }

    public function offsetUnset($offset)
    {
        throw new \MUtil\Lazy\LazyException('You cannot unset a Lazy object.');
    }
}