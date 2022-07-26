<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 201e Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model;

/**
 * A model that stores a nested data array in a session object
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class SessionModel extends \MUtil\Model\ArrayModelAbstract
{
    /**
     * When set to true in a subclass, then the model should be able to
     * save itself.
     *
     * @var boolean
     */
    protected $_saveable = true;

    /**
     * The namespace where the nested data is stored in array()
     *
     * @var \Zend_Session_Namespace
     */
    protected $_session;

    /**
     *
     * @param string $modelName Hopefully unique model name
     */
    public function __construct($modelName)
    {
        parent::__construct($modelName);

        $this->_session = new \Zend_Session_Namespace(__CLASS__ . '::' . $modelName);
        $this->_session->data = array();
    }

    /**
     * An ArrayModel assumes that (usually) all data needs to be loaded before any load
     * action, this is done using the iterator returned by this function.
     *
     * @return \Traversable Return an iterator over or an array of all the rows in this object
     */
    protected function _loadAllTraversable()
    {
        return $this->_session->data;
    }

    /**
     * When $this->_saveable is true a child class should either override the
     * delete() and save() functions of this class or override _saveAllTraversable().
     *
     * In the latter case this class will use _loadAllTraversable() and remove / add the
     * data to the data in the delete() / save() functions and pass that data on to this
     * function.
     *
     * @param array $data An array containing all the data that should be in this object
     * @return void
     */
    protected function _saveAllTraversable(array $data)
    {
        $this->_session->data = $data;
    }
}
