<?php

/**
 *
 * XmlRa class: pronouce "Ra" as "array" except on 19 september, then it is "ahrrray".
 *
 * @package    MUtil
 * @subpackage XmlRa
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 MagnaFacta BV
 * @license    New BSD License
 */

namespace MUtil\XmlRa;

/**
 * Basic iterator over the child elements of an XmlRa element.
 *
 * @package    MUtil
 * @subpackage XmlRa
 * @copyright  Copyright (c) 2013 MagnaFacta BV
 * @license    New BSD License
 * @since      Class available since version 1.3
 */
class XmlRaIterator implements \Iterator
{
    /**
     * The position of the current node
     *
     * @var int
     */
    private $_currentCount = -1;

    /**
     * The current xml item
     *
     * @var \MUtil\XmlRa
     */
    private $_currentNode;

    /**
     * Function for filtering the output results.
     *
     * Signature must be: function(mixed $value) where $value is
     * a \MUtil\XmlRa::_returnValue output and returns a boolean.
     *
     * @var callable
     */
    private $_filterFunction;

    /**
     * Function for transforming the outpur result.
     *
     * Signature must be: function(mixed $value) where $value is
     * a \MUtil\XmlRa::_returnValue output.
     *
     * @var callable
     */
    private $_mapFunction;

    /**
     * The start, i.e. parent xml item
     *
     * @var \MUtil\XmlRa
     */
    private $_startNode;

    /**
     * Initialize the iterator
     *
     * @param \MUtil\XmlRa $xmlra
     */
    public function __construct(\MUtil\XmlRa $xmlra)
    {
        $this->_startNode = $xmlra;
    }

    /**
     * Clean up the variables
     */
    public function __destruct()
    {
        unset($this->_currentCount, $this->_currentNode, $this->_startNode);
    }

    /**
     * \Iterator implementation for current child item
     *
     * @return \MUtil\XmlRa
     */
    public function current()
    {
        if (-1 === $this->_currentCount) {
            $this->next();
        }
        if ($this->_mapFunction) {
            return call_user_func($this->_mapFunction, $this->_currentNode);
        }
        return $this->_currentNode;
    }

    /**
     * \Iterator implementation, returns the index of the current item
     *
     * @return int
     */
    public function key()
    {
        if (-1 === $this->_currentCount) {
            $this->next();
        }
        return $this->_currentCount;
    }

    /**
     * Move the iterator one item further
     *
     * @return void
     */
    public function next()
    {
        $this->_currentCount++;

        if (isset($this->_startNode[$this->_currentCount])) {
            $this->_currentNode = $this->_startNode[$this->_currentCount];
        } else {
            $this->_currentNode = null;
        }

        if ($this->_currentNode && $this->_filterFunction) {
            if (!call_user_func($this->_filterFunction, $this->_currentNode)) {
                $this->next();
            }
        }
    }

    /**
     * Restart this iterator
     *
     * @return void
     */
    public function rewind()
    {
        $this->_currentCount = -1;
        $this->next();
    }

    /**
     * Set function for filtering the output results.
     *
     * Signature must be: function(mixed $value) where $value is
     * a \MUtil\XmlRa::_returnValue output and returns a boolean.
     *
     *
     * @param callable $function function()
     * @return \MUtil\XmlRa\XmlRaIterator (continuation pattern)
     */
    public function setFilterFunction($function)
    {
        $this->_filterFunction = $function;
        return $this;
    }

    /**
     * Set function for transforming the outpur result.
     *
     * Signature must be: function(mixed $value) where $value is
     * a \MUtil\XmlRa::_returnValue output.
     *
     * @param callable $function function()
     * @return \MUtil\XmlRa\XmlRaIterator (continuation pattern)
     */
    public function setMapFunction($function)
    {
        $this->_mapFunction = $function;
        return $this;
    }

    /**
     * Is there a current item
     *
     * @return boolean
     */
    public function valid()
    {
        if (-1 === $this->_currentCount) {
            $this->next();
        }
        return null !== $this->_currentNode;
    }
}
