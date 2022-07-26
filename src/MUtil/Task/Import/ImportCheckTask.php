<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Task_Import
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Task\Import;

/**
 *
 *
 * @package    MUtil
 * @subpackage Task_Import
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class ImportCheckTask extends \MUtil\Task\IteratorTaskAbstract
{
    /**
     * When false, the task is not added (for when just checking)
     *
     * @var boolean
     */
    protected $addImport = true;

    /**
     *
     * @var \MUtil\Task\TaskBatch
     */
    protected $importBatch;

    /**
     * The number of import errors after which the check is aborted.
     *
     * @var int
     */
    protected $importErrorsAllowed = 10;

    /**
     *
     * @var \MUtil\Model\ModelTranslatorInterface
     */
    protected $modelTranslator;

    /**
     * Should be called after answering the request to allow the Target
     * to check if all required registry values have been set correctly.
     *
     * @return boolean False if required values are missing.
     */
    public function checkRegistryRequestsAnswers()
    {
        return ($this->modelTranslator instanceof \MUtil\Model\ModelTranslatorInterface) &&
            parent::checkRegistryRequestsAnswers();
    }

    /**
     * Execute a single iteration of the task.
     *
     * @param scalar $key The current iterator key
     * @param mixed $current The current iterator content
     * @param array $params The parameters to the execute function
     */
    public function executeIteration($key, $current, array $params)
    {
        // \MUtil\EchoOut\EchoOut::track($key, $current);
        // Ignore empty rows.
        if (! $current) {
            return;
        }
        $batch = $this->getBatch();

        $row = $this->modelTranslator->translateRowValues($current, $key);

        if ($row) {
            $row = $this->modelTranslator->validateRowValues($row, $key);
        }
        $batch->addToCounter('import_checked');

        $errors = $this->modelTranslator->getRowErrors($key);
        foreach ($errors as $error) {
            $batch->addToCounter('import_errors');
            $batch->addMessage($error);
        }

        $errorCount = $batch->getCounter('import_errors');
        $checked    = $batch->getCounter('import_checked');
        $checkMsg   = sprintf($this->plural('%d row checked', '%d rows checked', $checked), $checked);

        // \MUtil\EchoOut\EchoOut::track($key, $row, $errors);

        if (0 === $errorCount) {
            // Do not report empty rows
            if ($row) {
                if ($this->addImport && $this->importBatch) {
                    // Let the translator decide how to save the row
                    $this->modelTranslator->addSaveTask($this->importBatch, $key, $row);
                }
                $batch->setMessage('check_status', sprintf($this->_('%s, no problems found.'), $checkMsg));
            }
        } else {
            $batch->setMessage('check_status', sprintf(
                    $this->plural('%s, one import problem found, continuing check.',
                            '%s, %d import problems found, continuing check.',
                            $errorCount),
                    $checkMsg,
                    $errorCount));
        }
    }


    /**
     * Return true when the task has finished.
     *
     * @return boolean
     */
    public function isFinished()
    {
        // Stop iterator when to many errors
        if ($this->getBatch()->getCounter('import_errors') >= $this->importErrorsAllowed) {
            return true;
        }

        return parent::isFinished();
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
        parent::setBatch($batch);

        if (! $this->importBatch instanceof \MUtil\Task\TaskBatch) {
            $this->importBatch = $batch;
        }

        return $this;
    }
}
