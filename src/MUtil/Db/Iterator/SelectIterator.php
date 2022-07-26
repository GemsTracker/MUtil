<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Db
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Db\Iterator;

/**
 *
 *
 * @package    MUtil
 * @subpackage Db
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class SelectIterator implements \Countable, \Iterator
{
    /**
     * The number of items
     *
     * @var int
     */
    protected $_count;

    /**
     * Current key
     *
     * @var int
     */
    protected $_i;

    /**
     *
     * @var array
     */
    protected $_row;

    /**
     *
     * @var type
     */
    protected $_select;

    /**
     *
     * @var \Zend_Db_Statement_Interface
     */
    protected $_statement;

    /**
     *
     * @param \Zend_Db_Select $select
     */
    public function __construct(\Zend_Db_Select $select)
    {
        $this->_select = $select;
    }

    protected function _initStatement()
    {
        // \MUtil\EchoOut\EchoOut::track($this->_select->__toString());

        $this->_i         = 0;
        $this->_statement = $this->_select->query();
        $this->_row       = $this->_statement->fetch();
    }

    /**
     * Count interface implementation
     * @return int
     */
    public function count(): int
    {
        if (null !== $this->_count) {
            return $this->_count;
        }

        // Why implement again what has already been done :)
        $pag = new \Zend_Paginator_Adapter_DbSelect($this->_select);
        $this->_count = $pag->count();

        return $this->_count;
    }


    /**
     * Return the current element
     *
     * @return array
     */
    public function current(): mixed
    {
        if (! $this->_statement instanceof \Zend_Db_Statement_Interface) {
            $this->_initStatement();
        }
        return $this->_row;
    }

    /**
     * Return the key of the current element
     *
     * @return int
     */
    public function key(): int
    {
        if (! $this->_statement instanceof \Zend_Db_Statement_Interface) {
            $this->_initStatement();
        }
        return $this->_i;
    }

    /**
     * Move forward to next element
     */
    public function next(): void
    {
        if (! $this->_statement instanceof \Zend_Db_Statement_Interface) {
            $this->_initStatement();
        }
        $this->_row = $this->_statement->fetch();
        $this->_i   = $this->_i + 1;
    }

    /**
     *  Rewind the \Iterator to the first element
     */
    public function rewind(): void
    {
        $this->_initStatement();
    }

    /**
     * True if not EOF
     *
     * @return boolean
     */
    public function valid(): bool
    {
        if (! $this->_statement instanceof \Zend_Db_Statement_Interface) {
            $this->_initStatement();
        }
        return (boolean) $this->_row;
    }

}
