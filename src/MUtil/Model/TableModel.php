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
 * A simple mode for a single table
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.2
 */
class TableModel extends \MUtil\Model\DatabaseModelAbstract
{
    /**
     *
     * @var \Zend_Db_Table_Abstract
     */
    private $_table;

    /**
     *
     * @param \Zend_Db_Table_Abstract $table An Zend abstract table or the table name
     * @param string $altName An alternative name to use, default is the name of the table itself
     */
    public function __construct($table, $altName = null)
    {
        if ($table instanceof \Zend_Db_Table_Abstract) {
            $this->_table = $table;
            $table_name = $this->_getTableName($table);
        } else {
            $this->_table = new \Zend_Db_Table($table);
            $table_name = $table;
        }

        parent::__construct(null === $altName ? $table_name : $altName);

        $this->_loadTableMetaData($this->_table);
    }

    /**
     * Save a single model item.
     *
     * @param array $newValues The values to store for a single model item.
     * @param array $filter If the filter contains old key values these are used
     * to decide on update versus insert.
     * @return array The values as they are after saving (they may change).
     */
    protected function _save(array $newValues, array $filter = null)
    {
        // $this->_saveTableData returns the new row values, including any automatic changes.
        // add $newValues to throw nothing away.
        return $this->_saveTableData($this->_table, $newValues, $filter, parent::SAVE_MODE_ALL) + $newValues;
    }

    /**
     * Delete items from the model
     *
     * @param mixed $filter True to use the stored filter, array to specify a different filter
     * @return int The number of items deleted
     */
    public function delete($filter = true)
    {
        return $this->_deleteTableData(
                $this->_table,
                $this->_checkFilterUsed($filter),
                $this->_deleteValues);
    }

    /**
     * The database adapter used by the model.
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->_table->getAdapter();
    }

    /**
     * Returns a \Zend_Db_Table_Select object to work with
     *
     * @return \Zend_Db_Table_Select
     */
    public function getSelect()
    {
        if ($this->hasItemsUsed()) {
            $select = $this->_table->select(\Zend_Db_Table_Abstract::SELECT_WITHOUT_FROM_PART);
            $select->from($this->_getTableName($this->_table), array());
            return $select;
        } else {
            return $this->_table->select(\Zend_Db_Table_Abstract::SELECT_WITH_FROM_PART);
        }
    }

    /**
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->_getTableName($this->_table);
    }
}
