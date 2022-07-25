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
 * Interface for external storage of stack
 *
 * @package    MUtil
 * @subpackage Batch
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
interface Stackinterface
{
    /**
     * Add an execution step to the command stack.
     *
     * @param string $method Name of a method of this object
     * @param array  $params Array with scalars, as many parameters as needed allowed
     * @return boolean When true, increment the number of commands, otherwise the command existed
     */
    public function addStep($method, array $params);

    /**
     * Return true when there still exist unexecuted commands
     *
     * @return boolean
     */
    public function hasNext();

    /**
     * Return the next command
     *
     * @return array 0 => command, 1 => params
     */
    public function getNext();

    /**
     * Run the next command
     *
     * @return void
     */
    public function gotoNext();

    /**
     * Register a class as being allowed in the stack
     *
     * @param string $className
     * @return \MUtil_Batch_Stack_StackInterface (continuation pattern)
     */
    public function registerAllowedClass($className);

    /**
     * Reset the stack
     *
     * @return \MUtil\Batch\Stack\Stackinterface (continuation pattern)
     */
    public function reset();

    /**
     * Add/set an execution step to the command stack. Named to prevent double addition.
     *
     * @param string $method Name of a method of the batch object
     * @param mixed $id A unique id to prevent double adding of something to do
     * @param array  $params Array with scalars, as many parameters as needed allowed
     * @return boolean When true, increment the number of commands, otherwise the command existed
     */
    public function setStep($method, $id, $params);
}
