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
 * Basic implementation of \MUtil\Task\TaskInterface, the interface for a Task object.
 * The \MUtil\Registry\TargetInterface allows the automatic loading of global objects.
 *
 * Task objects split large jobs into a number of serializeable small jobs that are
 * stored in the session or elsewhere and that can be executed one job at a time
 * split over multiple runs.
 *
 * @package    MUtil
 * @subpackage Task
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
abstract class TaskAbstract extends \MUtil\Translate\TranslateableAbstract
    implements \MUtil\Task\TaskInterface
{
    /**
     *
     * @var \MUtil\Task\TaskBatch
     */
    protected $_batch;

    /**
     * Should handle execution of the task, taking as much (optional) parameters as needed
     *
     * The parameters should be optional and failing to provide them should be handled by
     * the task
     */
    // public function execute();

    /**
     * Returns the batch this task belongs to
     *
     * @return \MUtil\Task\TaskBatch
     */
    public function getBatch()
    {
        if (! $this->_batch instanceof \MUtil\Task\TaskBatch) {
            throw new \MUtil\Batch\BatchException(sprintf(
                    "Batch not set during execution of task class %s!!",
                    __CLASS__
                    ));
        }

        return $this->_batch;
    }

    /**
     * Return true when the task has finished.
     *
     * @return boolean
     */
    public function isFinished()
    {
        return true;
    }

    /**
     * Sets the batch this task belongs to
     *
     * This method will be called from the \Gems\Task\TaskRunnerBatch upon execution of the
     * task. It allows the task to communicate with the batch queue.
     *
     * @param \MUtil\Task\TaskBatch $batch
     * @return \MUtil\Task\TaskInterface (continuation pattern)
     */
    public function setBatch(\MUtil\Task\TaskBatch $batch)
    {
        $this->_batch = $batch;
        return $this;
    }
}
