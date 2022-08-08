<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Task
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Task;

use Mezzio\Session\SessionInterface;
use MUtil\Batch\BatchAbstract;
use MUtil\Batch\BatchException;
use MUtil\Batch\Stack\Stackinterface;
use MUtil\Registry\TargetInterface;
use Psr\Log\LoggerInterface;
use Zalt\Loader\ProjectOverloader;

/**
 * The TaskBatch is an implementation of \MUtil\Batch\BatchAbstract that simplifies
 * batch creation by allowing each job step to be created in a seperate class.
 *
 * These tasks can automatically load global objects when they implement
 * \MUtil\Registry\TargetInterface. Otherwise you can pass only scalar values during
 * execution.
 *
 * Task are loaded through a plugin architecture, but you can also specify them using
 * their full class name.
 *
 * @see \MUtil\Batch\BatchAbstract
 * @see \MUtil\Registry\TargetInterface
 *
 * @package    MUtil
 * @subpackage Task
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class TaskBatch extends BatchAbstract
{
    public function __construct($id, ProjectOverloader $loader, SessionInterface $session, Stackinterface $stack = null, LoggerInterface $logger = null)
    {
        parent::__construct($id, $session, $stack, $logger);
        $this->overloader = $loader->createSubFolderOverloader('Task');
    }

    /**
     * Add a task to the stack, optionally adding as much parameters as needed
     *
     * @param string $task Name of Task class
     * @param mixed $param1 Optional scalar or array with scalars, as many parameters as needed allowed
     * @param mixed $param2 ...
     * @return \MUtil\Task\TaskBatch (continuation pattern)
     */
    public function addTask($task, $param1 = null)
    {
        $params = array_slice(func_get_args(), 1);
        $this->addStep('runTask', $task, $params);

        return $this;
    }

    /**
     *
     * @param string $task Class name of task
     * @param array $params Parameters used in the call to execute
     * @return boolean true when the task has completed, otherwise task is rerun.
     * @throws \MUtil\Batch\BatchException
     */
    public function runTask($task, array $params = array())
    {
        // \MUtil\EchoOut\EchoOut::track($task);

        $taskObject = $this->overloader->create($task);
        if ($taskObject instanceof TargetInterface) {
            // First set batch
            if ($taskObject instanceof TaskInterface) {
                $taskObject->setBatch($this);
            }
        }

        if ($taskObject instanceof TaskInterface) {
            call_user_func_array(array($taskObject, 'execute'), $params);

            return $taskObject->isFinished();

        } else {
            throw new BatchException(sprintf('ERROR: Task by name %s not found', $task));
        }
    }

    /**
     * Add an execution step to the command stack.
     *
     * @param string $task Name of Task class
     * @param mixed $id A unique id to prevent double adding of something to do
     * @param mixed $param1 Scalar or array with scalars, as many parameters as needed allowed
     * @return self
     */
    public function setTask($task, $id, $param1 = null)
    {
        $params = array_slice(func_get_args(), 2);
        $this->setStep('runTask', $id, $task, $params);

        return $this;
    }
}
