<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Iterator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Iterator;

/**
 *
 * @package    MUtil
 * @subpackage Model_Iterator
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class ArrayModelFilterIterator extends \FilterIterator
{
    /**
     * The filter to apply
     *
     * @var array
     */
    protected $_filter;

    /**
     *
     * @var \MUtil\Model\ArrayModelAbstract
     */
    protected $_model;

    /**
     *
     * @param \Iterator $iterator
     * @param \MUtil\Model\ArrayModelAbstract $model
     * @param array $filter
     */
    public function __construct(\Iterator $iterator, \MUtil\Model\ArrayModelAbstract $model, array $filter)
    {
        parent::__construct($iterator);

        $this->_model = $model;
        $this->_filter = $filter;
    }

    /**
     *
     * @return boolean
     */
    public function accept()
    {
        return $this->_model->applyFiltersToRow($this->current(), $this->_filter);
    }
}
