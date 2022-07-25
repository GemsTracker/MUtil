<?php

/**
 *
 * @package    MUtil
 * @subpackage SaveToModel
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 201e Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Task\Import;

/**
 *
 *
 * @package    MUtil
 * @subpackage SaveToModel
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class SaveToModel extends \MUtil\Task\TaskAbstract
{
    /**
     *
     * @var \MUtil\Model\ModelAbstract
     */
    protected $targetModel;

    /**
     * Should be called after answering the request to allow the Target
     * to check if all required registry values have been set correctly.
     *
     * @return boolean False if required values are missing.
     */
    public function checkRegistryRequestsAnswers()
    {
        return ($this->targetModel instanceof \MUtil\Model\ModelAbstract) &&
            parent::checkRegistryRequestsAnswers();
    }

    /**
     * Should handle execution of the task, taking as much (optional) parameters as needed
     *
     * The parameters should be optional and failing to provide them should be handled by
     * the task
     *
     * @param array $row Row to save
     */
    public function execute($row = null)
    {
        if ($row) {
            $batch = $this->getBatch();
            $batch->addToCounter('imported');

            $oldCount = $this->targetModel->getChanged();
            
            // \MUtil\EchoOut\EchoOut::track($row);
            $this->targetModel->save($row);
            $batch->addToCounter('changed', $this->targetModel->getChanged() - $oldCount);
        }
    }
}
