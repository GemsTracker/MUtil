<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model;

use Zalt\Model\Data\DataReaderInterface;

/**
 * Translators can translate the data from one model to be saved using another
 * model.
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Interface available since \MUtil version 1.3
 */
interface ModelTranslatorInterface extends \MUtil\Registry\TargetInterface, \Zalt\Model\Translator\ModelTranslatorInterface
{
    /**
     * Add the current row to a (possibly separate) batch that does the importing.
     *
     * @param \MUtil\Task\TaskBatch $importBatch The import batch to impor this row into
     * @param string $key The current iterator key
     * @param array $row translated and validated row
     * @return \MUtil\Model\ModelTranslatorAbstract (continuation pattern)
     */
    public function addSaveTask(\MUtil\Task\TaskBatch $importBatch, $key, array $row);

    /**
     * Returns a description of the translator errors for the row specified.
     *
     * @param mixed $row
     * @return array of String messages
     */
    public function getRowErrors($row): array;

    /**
     * Get the source model, where the data is coming from.
     *
     * @return DataReaderInterface $sourceModel The source of the data
     */
    public function getSourceModel(): DataReaderInterface;

    /**
     * Set the source model, where the data is coming from.
     *
     * @param DataReaderInterface $sourceModel The source of the data
     * @return \Zalt\Model\Translator\ModelTranslatorInterface (continuation pattern)
     */
    public function setSourceModel(DataReaderInterface $sourceModel): ModelTranslatorInterface;
}
