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
 *
 * @package    MUtil
 * @subpackage Batch
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
abstract class StackAbstract implements \MUtil\Batch\Stack\Stackinterface
{
    /**
     * The classes allowed in the stack
     *
     * @var \MUtil\Util\ClassList
     */
    protected $_allowedClasses;

    /**
     * Add/set the command to the stack
     *
     * @param array $command
     * @param string $id Optional id to repeat double execution
     * @return boolean When true, increment the number of commands, otherwise the command existed
     */
    abstract protected function _addCommand(array $command, $id = null);

    protected function _checkParams(array $params)
    {
        $checks = \MUtil\Ra::nonScalars($params);
        if ($this->_allowedClasses) {
            $checks = \MUtil\Ra::nonScalars($params);
            if (is_array($checks)) {
                foreach ($checks as $object) {
                    if (! $this->_allowedClasses->get($object)) {
                        $name   = get_class($object);
                        $method = reset($params);
                        throw new \MUtil\Batch\BatchException("Not allowed batch class $name parameter for method: '$method'.");
                    }
                }
            }

        } elseif (! \MUtil\Ra::isScalar($params)) {
            $checks = \MUtil\Ra::nonScalars($params);
            if (is_array($checks)) {
                $object = reset($checks);
                $name   = get_class($object);
                $method = reset($params);
                throw new \MUtil\Batch\BatchException("Not allowed batch class $name parameter for method: '$method'.");
            }
        }
    }

    /**
     * Make sure the allowed class list exists
     *
     * @return void
     */
    protected function _ensureAllowedClassList()
    {
        if (! $this->_allowedClasses) {
            $this->_allowedClasses = new \MUtil\Util\ClassList();
        }
    }

    /**
     * Add an execution step to the command stack.
     *
     * @param string $method Name of a method of the batch object
     * @param array  $params Array with scalars, as many parameters as needed allowed
     * @return boolean When true, increment the number of commands, otherwise the command existed
     */
    public function addStep(string $method, array $params): bool
    {
        $this->_checkParams($params);

        return $this->_addCommand(array($method, $params));
    }

    /**
     * Return the next command
     *
     * @return array()
     */
    // public function getNext(): array;

    /**
     * Run the next command
     *
     * @param mixed $batch Should be \MUtil\Batch\BatchAbstract but could be changed in implementations
     * @return void
     */
    // public function gotoNext($batch): void;

    /**
     * Return true when there still exist unexecuted commands
     *
     * @return boolean
     */
    // public function hasNext(): bool

    /**
     * Register a class as being allowed in the stack
     *
     * @param string $className
     * @return self
     */
    public function registerAllowedClass(string $className): self
    {
        if (! $this->_allowedClasses) {
            $this->_ensureAllowedClassList();
        }
        if (is_object($className)) {
            $className = get_class($className);
        }
        $this->_allowedClasses->set($className, true);

        return $this;
    }

    /**
     * Reset the stack
     *
     * @return \MUtil\Batch\Stack\Stackinterface (continuation pattern)
     */
    // public function reset()

    /**
     * Add/set an execution step to the command stack. Named to prevent double addition.
     *
     * @param string $method Name of a method of the batch object
     * @param mixed $id A unique id to prevent double adding of something to do
     * @param array  $params Array with scalars, as many parameters as needed allowed
     * @return boolean When true, increment the number of commands, otherwise the command existed
     */
    public function setStep(string $method, ?string $id, array $params): bool
    {
        $this->_checkParams($params);

        return $this->_addCommand(array($method, $params), $id);
    }
}
