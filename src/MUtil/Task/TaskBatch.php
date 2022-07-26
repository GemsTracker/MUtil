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
class TaskBatch extends \MUtil\Batch\BatchAbstract
{
    /**
     *
     * @var \MUtil\Registry\SourceInterface
     */
    protected $source;

    /**
     *
     * @var \MUtil\Loader\PluginLoader
     */
    protected $taskLoader;

    /**
     *
     * @var array containing the classPrefix => classPath for task laoder
     */
    protected $taskLoaderDirs = array('MUtil_Task' => 'MUtil/Task');

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
     * Add the directories to be used by this instance, existing instance are overrule
     * by new directories
     *
     * @param array $dirs An array containing the classPrefix => classPath values
     * @return \MUtil\Task\TaskBatch (continuation pattern)
     */
    public function addTaskLoaderPrefixDirectories(array $dirs)
    {
        $this->taskLoaderDirs = $this->taskLoaderDirs + $dirs;
        return $this;
    }

    /**
     * Return the source used to set variables in tasks.
     *
     * @return \MUtil\Registry\SourceInterface
     */
    public function getSource()
    {
        if (! $this->source) {
            $this->setSource(new \MUtil\Registry\Source());
        }
        return $this->source;
    }

    /**
     * Get the plugin loader to load the tasks
     *
     * @return  \MUtil\Loader\PluginLoader
     */
    public function getTaskLoader()
    {
        // \MUtil\EchoOut\EchoOut::track($this->getTaskLoaderPrefixDirectories());
        if (! $this->taskLoader) {
            $this->setTaskLoader(new \MUtil\Loader\PluginLoader($this->getTaskLoaderPrefixDirectories()));
        }

        return $this->taskLoader;
    }

    /**
     * Returns an array containing the classPrefix => classPath values
     * to be used by this instance
     *
     * @return array
     */
    public function getTaskLoaderPrefixDirectories()
    {
        return $this->taskLoaderDirs;
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

        $taskObject = $this->getTaskLoader()->createClass($task);
        if ($taskObject instanceof \MUtil\Registry\TargetInterface) {
            // First set batch
            if ($taskObject instanceof \MUtil\Task\TaskInterface) {
                $taskObject->setBatch($this);
            }
            if (!$this->getSource()->applySource($taskObject)) {
                throw new \MUtil\Batch\BatchException(sprintf('ERROR: Parameters failed to load for task %s.', $task));
            }
        }

        if ($taskObject instanceof \MUtil\Task\TaskInterface) {
            call_user_func_array(array($taskObject, 'execute'), $params);

            return $taskObject->isFinished();

        } else {
            throw new \MUtil\Batch\BatchException(sprintf('ERROR: Task by name %s not found', $task));
        }
    }

    /**
     * Store a variable in the session store.
     *
     * @param string $name Name of the variable
     * @param mixed $variable Something that can be serialized
     * @return \MUtil\Batch\BatchAbstract (continuation pattern)
     */
    public function setSessionVariable($name, $variable)
    {
        parent::setSessionVariable($name, $variable);

        $this->source->addRegistryContainer($this->getSessionVariables(), 'source');

        return $this;
    }

    /**
     * Set the variable source for tasks.
     *
     * @param \MUtil\Registry\SourceInterface $source
     * @return \MUtil\Task\TaskBatch (continuation pattern)
     */
    public function setSource(\MUtil\Registry\SourceInterface $source)
    {
        $this->source = $source;

        $session = $this->getSessionVariables();
        if ($session) {
            $this->source->addRegistryContainer($session, 'source');
        }
        if ($this->variables) {
            $this->source->addRegistryContainer($this->variables, 'variables');
        }

        return $this;
    }

    /**
     * Add an execution step to the command stack.
     *
     * @param string $task Name of Task class
     * @param mixed $id A unique id to prevent double adding of something to do
     * @param mixed $param1 Scalar or array with scalars, as many parameters as needed allowed
     * @return \MUtil\Task\TaskBatch (continuation pattern)
     */
    public function setTask($task, $id, $param1 = null)
    {
        $params = array_slice(func_get_args(), 2);
        $this->setStep('runTask', $id, $task, $params);

        return $this;
    }

    /**
     * Set the plugin loader to load the tasks
     *
     * @param \MUtil\Loader\PluginLoader $taskLoader
     * @return \MUtil\Task\TaskBatch (continuation pattern)
     */
    public function setTaskLoader(\MUtil\Loader\PluginLoader $taskLoader)
    {
        $this->taskLoader = $taskLoader;
        return $this;
    }

    /**
     * Set the directories to be used by this instance
     *
     * @param array $dirs An array containing the classPrefix => classPath values
     * @return \MUtil\Task\TaskBatch (continuation pattern)
     */
    public function setTaskLoaderPrefixDirectories(array $dirs)
    {
        $this->taskLoaderDirs = $dirs;
        return $this;
    }

    /**
     * Store a variable in the general store.
     *
     * These variables have to be reset for every run of the batch.
     *
     * @param string $name Name of the variable
     * @param mixed $variable Something that can be serialized
     * @return \MUtil\Batch\BatchAbstract (continuation pattern)
     */
    public function setVariable($name, $variable)
    {
        parent::setVariable($name, $variable);

        $this->source->addRegistryContainer($this->variables, 'variables');

        return $this;
    }
}
