<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage \Iterator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Iterator;

/**
 * Calls a function for each item in an iterator before returning it
 *
 * @package    MUtil
 * @subpackage \Iterator
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.7.2 28-okt-2015 17:34:25
 */
class ItemCallbackIterator implements \OuterIterator, \Countable
{
    /**
     *
     * @var callable
     */
    private $_callback;

    /**
     *
     * @var \Iterator
     */
    private $_iterator;

    /**
     *
     * @param \Traversable $iterator
     * @param Callable $callback
     */
    public function __construct(\Traversable $iterator, $callback)
    {
        $this->_iterator = $iterator;
        while ($this->_iterator instanceof \IteratorAggregate) {
            $this->_iterator = $this->_iterator->getIterator();
        }

        $this->_callback = $callback;
    }

    /**
     * Count elements of an object
     *
     * Rewinding version of count
     *
     * @return int
     */
    public function count(): int
    {
        if ($this->_iterator instanceof \Countable) {
            return $this->_iterator->count();
        }

        $count = iterator_count($this->_iterator);
        $this->_iterator->rewind();
        return $count;
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return call_user_func($this->_callback, $this->_iterator->current());
    }

    /**
     * Returns the inner iterator for the current entry.
     *
     * @return \Iterator
     */
    public function getInnerIterator(): ?\Iterator
    {
        return $this->_iterator;
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return $this->_iterator->key();
    }

    /**
     * Move forward to next element
     */
    public function next(): void
    {
        $this->_iterator->next();
    }

    /**
     * Rewind the \Iterator to the first element
     */
    public function rewind(): void
    {
        $this->_iterator->rewind();
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean
     */
    public function valid(): bool
    {
        return $this->_iterator->valid();
    }

}
