<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage CheckCounterTask
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 201e Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Task;

/**
 * Stop the batch if a counter has reached a threshold.
 *
 * @package    MUtil
 * @subpackage CheckCounterTask
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class CheckCounterTask extends \MUtil\Task\TaskAbstract
{
    /**
     * Should handle execution of the task, taking as much (optional) parameters as needed
     *
     * The parameters should be optional and failing to provide them should be handled by
     * the task
     *
     * @param string $counterName Name of a counter
     * @param string $message A stop message, sprintf-ed with paramters: $counterName, $counter,
     *                        $threshold, $threshold - $counter
     * @param int $threshold Threshold to trigger the counter
     */
    public function execute($counterName = 'errors', $message = null, $threshold = 1)
    {
        $batch   = $this->getBatch();
        $counter = $batch->getCounter($counterName);
        
        if ($counter >= $threshold) {
            if (null === $message) {
                $message = "Threshold for %s reached, stopped batch.";
            }

            $batch->stopBatch(sprintf($message, $counterName, $counter, $threshold, $threshold - $counter));
        }
    }
}
