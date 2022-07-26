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
 *
 *
 * @package    MUtil
 * @subpackage Task
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
abstract class IteratorTaskAbstract extends \MUtil\Task\TaskAbstract
{
    /**
     *
     * @var \Iterator
     */
    protected $iterator;

    /**
     * Should be called after answering the request to allow the Target
     * to check if all required registry values have been set correctly.
     *
     * @return boolean False if required values are missing.
     */
    public function checkRegistryRequestsAnswers()
    {
        return ($this->iterator instanceof \Iterator) &&
            parent::checkRegistryRequestsAnswers();
    }

    /**
     * Should handle execution of the task, taking as much (optional) parameters as needed
     *
     * The parameters should be optional and failing to provide them should be handled by
     * the task
     */
    public function execute()
    {
        if ($this->getBatch()->getCounter('iterstarted') === 0) {
            $this->getBatch()->addToCounter('iterstarted');
            if ($this->iterator instanceof \Countable) {
                // Add the count - 1 as this task already added 1 for this run
                $this->getBatch()->addStepCount(count($this->iterator) -1);
            }
        }
        $this->executeIteration($this->iterator->key(), $this->iterator->current(), func_get_args());
    }

    /**
     * Execute a single iteration of the task.
     *
     * @param scalar $key The current iterator key
     * @param mixed $current The current iterator content
     * @param array $params The parameters to the execute function
     */
    abstract public function executeIteration($key, $current, array $params);

    /**
     * Return true when the task has finished.
     *
     * @return boolean
     */
    public function isFinished()
    {
        $this->iterator->next();
        $result = ! $this->iterator->valid();
        
        if ($result === true) {
            $this->getBatch()->resetCounter('iterstarted');
        } else {
            if (! ($this->iterator instanceof \Countable)) {
                // Add 1 to the counter to keep existing behaviour
                $this->getBatch()->addStepCount(1);
            }
        }
        return $result;
    }
}
