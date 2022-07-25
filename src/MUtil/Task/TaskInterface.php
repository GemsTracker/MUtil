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
 * The interface for a Task object. Task objects split large jobs into a number
 * of serializeable small jobs that are stored in the session or elsewhere
 * and that can be executed one job at a time split over multiple runs.
 *
 * @package    MUtil
 * @subpackage Task
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
interface TaskInterface
{
    /**
     * Should handle execution of the task, taking as much (optional) parameters as needed
     *
     * The parameters should be optional and failing to provide them should be handled by
     * the task
     */
    public function execute();

    /**
     * Return true when the task has finished.
     *
     * @return boolean
     */
    public function isFinished();

    /**
     * Sets the batch this task belongs to
     *
     * This method will be called from the \Gems\Task\TaskRunnerBatch upon execution of the
     * task. It allows the task to communicate with the batch queue.
     *
     * @param \MUtil\Task\TaskBatch $batch
     * @return \MUtil\Task\TaskInterface (continuation pattern)
     */
    public function setBatch(\MUtil\Task\TaskBatch $batch);
}
