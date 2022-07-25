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
interface ModelTranslatorInterface extends \MUtil\Registry\TargetInterface
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
     * Returns a description of the translator to enable users to choose
     * the translator they need.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns error messages from the transformation.
     *
     * @return array of String messages
     */
    public function getErrors();

    /**
     * Get information on the field translations
     *
     * @return array of fields sourceName => targetName
     * @throws \MUtil\Model\ModelException
     */
    public function getFieldsTranslations();

    /**
     * Returns an array of the field names that are required
     *
     * @return array of fields sourceName => targetName
     */
    public function getRequiredFields();

    /**
     * Returns a description of the translator errors for the row specified.
     *
     * @param mixed $row
     * @return array of String messages
     */
    public function getRowErrors($row);

    /**
     * Get the source model, where the data is coming from.
     *
     * @return \MUtil\Model\ModelAbstract $sourceModel The source of the data
     */
    public function getSourceModel();

    /**
     * Get a form for filtering and validation, populating it
     * with elements.
     *
     * @return \Zend_Form
     */
    public function getTargetForm();

    /**
     * Get the target model, where the data is going to.
     *
     * @return \MUtil\Model\ModelAbstract $sourceModel The target of the data
     */
    public function getTargetModel();

    /**
     * True when the transformation generated errors.
     *
     * @return boolean True when there are errora
     */
    public function hasErrors();

    /**
     * Set the source model, where the data is coming from.
     *
     * @param \MUtil\Model\ModelAbstract $sourceModel The source of the data
     * @return \MUtil\Model\ModelTranslatorAbstract (continuation pattern)
     */
    public function setSourceModel(\MUtil\Model\ModelAbstract $sourceModel);

    /**
     * Set the target model, where the data is going to.
     *
     * @param \MUtil\Model\ModelAbstract $sourceModel The target of the data
     * @return \MUtil\Model\ModelTranslatorAbstract (continuation pattern)
     */
    public function setTargetModel(\MUtil\Model\ModelAbstract $targetModel);

    /**
     * Prepare for the import.
     *
     * @return \MUtil\Model\ModelTranslatorAbstract (continuation pattern)
     */
    public function startImport();

    /**
     * Perform any translations necessary for the code to work
     *
     * @param mixed $row array or \Traversable row
     * @param scalar $key
     * @return mixed Row array or false when errors occurred
     */
    public function translateRowValues($row, $key);

    /**
     * Validate the data against the target form
     *
     * @param array $row
     * @param scalar $key
     * @return mixed Row array or false when errors occurred
     */
    public function validateRowValues(array $row, $key);
}
