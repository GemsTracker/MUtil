<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model;

/**
 * This class wraps around a select as a paginator, while allowing model->onload
 * functions to apply.
 *
 * It also implements some extra fancy functions to speed up the result retrieval on MySQL databases.
 *
 * @see \MUtil\Model\DatabaseModelAbstract
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class SelectModelPaginator implements \MUtil\Paginator\Adapter\PrefetchInterface
{
    /**
     * Store for count
     *
     * @var int
     */
    protected $_count;

    /**
     * Last item count
     *
     * @var int
     */
    protected $_lastItemCount = null;

    /**
     * Last items for last offset
     *
     * @var \Traversable
     */
    protected $_lastItems = null;

    /**
     * Last offset
     *
     * @var int
     */
    protected $_lastOffset = null;

    /**
     *
     * @var \MUtil\Model\DatabaseModelAbstract
     */
    protected $_model;

    /**
     *
     * @var \Zend_Db_Select
     */
    protected $_select;

    /**
     *
     * @var \Zend_Paginator_Adapter_DbSelect
     */
    protected $_selectAdapter;

    /**
     *
     * @param \Zend_Db_Select $select
     * @param \MUtil\Model\ModelAbstract $model
     */
    public function __construct(\Zend_Db_Select $select, \MUtil\Model\DatabaseModelAbstract $model)
    {
        $this->_select = $select;
        $this->_selectAdapter = new \Zend_Paginator_Adapter_DbSelect($select);
        $this->_model = $model;
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @return integer
     */
    public function count(): int
    {
        if (null === $this->_count) {
            $this->_count = $this->_selectAdapter->count();
        }

        return $this->_count;
    }

    /**
     * Returns an array of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        // Cast to integers, as $itemCountPerPage can be string sometimes and that would fail later checks
        $offset = (int) $offset;
        $itemCountPerPage = (int) $itemCountPerPage;

        if (($this->_lastOffset === $offset) &&
                ($this->_lastItemCount === $itemCountPerPage) &&
                (null !== $this->_lastItems)) {

            return $this->_lastItems;
        }
        $this->_lastOffset    = $offset;
        $this->_lastItemCount = $itemCountPerPage;

        // Optimization: by using the MySQL feature SQL_CALC_FOUND_ROWS
        // we can get the count and the results in a single query.
        $db = $this->_select->getAdapter();
        if ((null === $this->_count) && ($db instanceof \Zend_Db_Adapter_Mysqli)) {

            $this->_select->limit($itemCountPerPage, $offset);
            $sql = $this->_select->__toString();

            if (\MUtil\StringUtil\StringUtil::startsWith($sql, 'select ', true)) {
                $sql = 'SELECT SQL_CALC_FOUND_ROWS ' . substr($sql, 7);
            }

            $this->_lastItems = $db->fetchAll($sql);

            $this->_count = $db->fetchOne('SELECT FOUND_ROWS()');

        } else {
            $this->_lastItems = $this->_selectAdapter->getItems($offset, $itemCountPerPage);
        }

        if (is_array($this->_lastItems)) {
            if (isset($this->_model->prefetchIterator) && $this->_model->prefetchIterator) {
                $this->_lastItems = new \ArrayIterator($this->_lastItems);
            }

            $this->_lastItems = $this->_model->processAfterLoad($this->_lastItems, false, false);
        }

        return $this->_lastItems;
    }
}
