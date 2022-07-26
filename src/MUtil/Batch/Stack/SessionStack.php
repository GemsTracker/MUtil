<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Batch
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Batch\Stack;

/**
 * A default command stack that uses the session ot store the commands to
 * execute.
 *
 * @package    MUtil
 * @subpackage Batch
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class SessionStack extends \MUtil\Batch\Stack\StackAbstract
{
    /**
     *
     * @var \Zend_Session_Namespace
     */
    private $_session;

    /**
     *
     * @param string $id A unique name identifying the batch
     */
    public function __construct($id)
    {
        $this->_session = new \Zend_Session_Namespace(get_class($this) . '_' . $id);

        if (! isset($this->_session->commands)) {
            $this->_session->commands = array();
        }
    }

    /**
     * Add/set the command to the stack
     *
     * @param array $command
     * @param string $id Optional id to repeat double execution
     * @return boolean When true, increment the number of commands, otherwise the command existed
     */
    protected function _addCommand(array $command, $id = null)
    {
        $result = (null === $id) || !isset($this->_session->commands[$id]);

        if (null === $id) {
            $this->_session->commands[] = $command;
        } else {
            $this->_session->commands[$id] = $command;
        }

        return $result;
    }

    /**
     * Return the next command
     *
     * @return array 0 => command, 1 => params
     */
    public function getNext()
    {
        return reset($this->_session->commands);
    }

    /**
     * Run the next command
     *
     * @return void
     */
    public function gotoNext()
    {
        array_shift($this->_session->commands);
    }

    /**
     * Return true when there still exist unexecuted commands
     *
     * @return boolean
     */
    public function hasNext()
    {
        return (boolean) $this->_session->commands;
    }

    /**
     * Reset the stack
     *
     * @return \MUtil\Batch\Stack\Stackinterface (continuation pattern)
     */
    public function reset()
    {
        $this->_session->commands = array();

        return $this;
    }
}
