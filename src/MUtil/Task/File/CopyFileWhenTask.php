<?php

/**
 *
 * @package    MUtil
 * @subpackage Task
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 201e Erasmus MC
 * @license    New BSD License
 */

/**
 *
 *
 * @package    MUtil
 * @subpackage Task
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.3
 */
class MUtil_Task_File_CopyFileWhenTask extends \MUtil_Task_TaskAbstract
{
    /**
     * Should handle execution of the task, taking as much (optional) parameters as needed
     *
     * The parameters should be optional and failing to provide them should be handled by
     * the task
     *
     * @param string $file The file to move
     * @param string $destination The destionation (folder or new name
     * @param string $counter Name of coutner threshold - or leave empty for always move
     * @param int $min Optional minimum counter value
     * @param int $max Optional maximum counter value (use either minimum or both)
     */
    public function execute($file = null, $destination = null, $counter = null, $min = null, $max = null)
    {
        if (! file_exists($file)) {
            return;
        }

        $batch = $this->getBatch();
        if ($counter) {
            $value = $batch->getCounter($counter);

            if ((null !== $min) && ($value < $min)) {
                return;
            }
            if ((null !== $max) && ($value > $max)) {
                return;
            }
        }

        if (is_dir($destination)) {
            $destination = $destination . DIRECTORY_SEPARATOR . basename($file);
        }
        \MUtil_File::ensureDir(dirname($destination));

        if (@copy($file, $destination)) {
            $batch->addMessage(sprintf($this->_('Copied file to "%s".'), basename($destination)));
        } else {
            $batch->addMessage(sprintf(
                    $this->_('Could not copy "%s" to "%s".'),
                    basename($file),
                    basename($destination)
                    ));
        }
    }
}
