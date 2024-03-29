<?php

/**
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

/**
 * Utility object for importing data from one model to another
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.3
 */
class MUtil_Model_Importer extends \MUtil_Translate_TranslateableAbstract
{
    /**
     * The extension used for the import
     *
     * @var string
     */
    private $_extension;

    /**
     * The filename of the import file.
     *
     * @var string
     */
    private $_filename;

    /**
     *
     * @var \MUtil_Task_TaskBatch
     */
    protected $_importBatch;

    /**
     * The final directory for when the data could not be imported.
     *
     * If empty the file is thrown away after a failure.
     *
     * @var string
     */
    public $failureDirectory;

    /**
     * The translator to use
     *
     * @var \MUtil_Model_ModelTranslatorInterface
     */
    protected $importTranslator;

    /**
     * The filename minus the extension for long term storage.
     *
     * If empty the file is not renamed and may overwrite an existing file.
     *
     * @var string
     */
    protected $longtermFilename;

    /**
     * Registry source
     *
     * @var \MUtil_Registry_SourceInterface
     */
    protected $registrySource;

    /**
     * Model to read import
     *
     * @var \MUtil_Model_ModelAbstract
     */
    protected $sourceModel;

    /**
     * The final directory when the data was successfully imported.
     *
     * If empty the file is thrown away after the import.
     *
     * @var string
     */
    public $successDirectory;

    /**
     * Model to save import into
     *
     * Required, can be set by passing a model to $this->model
     *
     * @var \MUtil_Model_ModelAbstract
     */
    protected $targetModel;

    /**
     * Set the variables 
     * @param \MUtil_Task_TaskBatch $batch
     */
    protected function addVariablesToBatch(\MUtil_Task_TaskBatch $batch)
    {
        $batch->getStack()->registerAllowedClass('MUtil_Date');
        
        if ($batch->hasVariable('targetModel')) {
            // Already set
            return;
        }

        $targetModel = $this->getTargetModel();
        $batch->setVariable('targetModel', $targetModel);

        $importTranslator = $this->getImportTranslator();
        $importTranslator->setTargetModel($targetModel);
        $importTranslator->startImport();
        $batch->setVariable('modelTranslator', $importTranslator);

        // Load the iterator when it is not loaded OR
        // the iterator itself is no lnger valid!
        $loadIter = true;
        if ($batch->hasSessionVariable('iterator')) {
            $iter = $batch->getSessionVariable('iterator');
            if ($iter instanceof \Iterator) {
                $loadIter = ! $iter->valid();
            }
        }

        if ($loadIter) {
            $iter = $this->getSourceModel()->loadIterator();

            if (($iter instanceof \Iterator) && ($iter instanceof \Serializable)) {
                $batch->setSessionVariable('iterator', $iter);
            } else {
                $batch->setVariable('iterator', $iter);

                if ($batch->isPull()) {
                    // Cannot pull when iterator is not serializable
                    $batch->setMethodPush();
                }
            }
        }        
    }

    /**
     * Clear the final directory for when the data could not be imported.
     *
     * The file is thrown away after a failure.
     *
     * @return \MUtil_Model_Importer (continuation pattern)
     */
    public function clearFailureDirectory()
    {
        return $this->setFailureDirectory();
    }

    /**
     * Clears the filename for long term storage. The file is not renamed and
     * may overwrite an existing file.
     *
     * @return \MUtil_Model_Importer (continuation pattern)
     */
    public function clearLongtermFilename()
    {
        return $this->setLongtermFilename();
    }

    /**
     * Clear the directory for when the data was successfully imported.
     *
     * The the file is thrown away after the import.
     *
     * @return \MUtil_Model_Importer (continuation pattern)
     */
    public function clearSuccessDirectory()
    {
        return $this->setSuccessDirectory();
    }

    /**
     *
     * @param string $idPart End part for batch id
     * @param \MUtil_Task_TaskBatch $batch Optional batch with different source etc..
     * @return \MUtil_Task_TaskBatch
     */
    protected function getBasicImportBatch($idPart, \MUtil_Task_TaskBatch $batch = null)
    {
        if (null === $batch) {
            $batch = new \MUtil_Task_TaskBatch('check_' . basename($this->sourceModel->getName()) . '_' . $idPart);
            $this->registrySource->applySource($batch);
            $batch->setSource($this->registrySource);
        }

        $this->addVariablesToBatch($batch);

        return $batch;
    }


    /**
     *
     * @param \MUtil_Task_TaskBatch $batch Optional batch with different source etc..
     * @return \MUtil_Task_TaskBatch
     */
    public function getCheckAndImportBatch(\MUtil_Task_TaskBatch $batch = null)
    {
        $batch = $this->getBasicImportBatch(__FUNCTION__, $batch);

        if (! $batch->isLoaded()) {
            $batch->addTask('Import_ImportCheckTask');
            if ($this->_filename) {
                $batch->addTask(
                        'File_CopyFileWhenTask',
                        $this->_filename,
                        $this->getFailureDirectory() . DIRECTORY_SEPARATOR . $this->getLongtermFilename() . '.' . $this->_extension,
                        'import_errors',
                        1);
            }
            $batch->addTask('CheckCounterTask', 'import_errors', $this->_('Found %2$d import error(s). Import aborted.'));
            if ($this->_filename) {
                $batch->addTask(
                        'AddTask', // AddTask task as when all is OK this task should be added
                        'File_CopyFileWhenTask',
                        $this->_filename,
                        $this->getSuccessDirectory() . DIRECTORY_SEPARATOR . $this->getLongtermFilename() . '.' . $this->_extension,
                        'import_errors',
                        0,
                        0);
            }
        }

        return $batch;
    }

    /**
     *
     * @param \MUtil_Task_TaskBatch $chechkBatch Optional check batch with different source etc..
     * @param \MUtil_Task_TaskBatch $importBatch Optional import batch with different source etc..
     * @return \MUtil_Task_TaskBatch
     */
    public function getCheckWithImportBatches(\MUtil_Task_TaskBatch $checkBatch = null, \MUtil_Task_TaskBatch $importBatch = null)
    {
        $batch = $this->getBasicImportBatch(__FUNCTION__, $checkBatch);

        if (! $batch->isLoaded()) {
            $batch->addTask('Import_ImportCheckTask');
        }
        $batch->setVariable('importBatch', $this->getImportOnlyBatch($importBatch));

        return $batch;
    }

    /**
     * Get the final directory for when the data could not be imported.
     *
     * If empty the file is thrown away after the failure.
     *
     * @return string String or null when there is no failure storage
     */
    public function getFailureDirectory()
    {
        return $this->failureDirectory;
    }

    /**
     * Get the current translator, if set
     *
     * @return \MUtil_Model_ModelTranslatorInterface or null
     */
    public function getImportTranslator()
    {
        return $this->importTranslator;
    }

    /**
     *
     * @param \MUtil_Task_TaskBatch $batch Optional batch with different source etc..
     * @return \MUtil_Task_TaskBatch
     */
    public function getImportOnlyBatch(\MUtil_Task_TaskBatch $batch = null)
    {
        if (! $this->_importBatch instanceof \MUtil_Task_TaskBatch) {
            $batch = new \MUtil_Task_TaskBatch(__CLASS__ . '_import_' . basename($this->sourceModel->getName()) . '_' . __FUNCTION__);

            $this->registrySource->applySource($batch);
            $batch->setSource($this->registrySource);
            
            $this->_importBatch = $batch;
        }
        
        $batch = $this->_importBatch;
                
        $this->addVariablesToBatch($batch);        

        if (! $batch->isLoaded()) {
            if ($this->_filename) {
                $batch->addTask(
                        'AddTask', // AddTask task as when all is OK this task should be added
                        'File_CopyFileWhenTask',
                        $this->_filename,
                        $this->getSuccessDirectory() . DIRECTORY_SEPARATOR . $this->getLongtermFilename() . '.' . $this->_extension,
                        'import_errors',
                        0,
                        0);
            }
            // Rest of loading is done by getCheckOnlyBatch, but when started, the above task must be added.
        }
        return $batch;
    }

    /**
     * Get the filename minus the extension for long term storage.
     *
     * If empty the file is not renamed and may overwrite an existing file.
     *
     * @return string String or null when there is no renaming
     */
    public function getLongtermFilename()
    {
        return $this->longtermFilename;
    }

    /**
     * Get the data source for items created this importer (if any)
     *
     * @return \MUtil_Registry_SourceInterface
     */
    public function getRegistrySource()
    {
        return $this->registrySource;
    }

    /**
     * Get the source model that provides the import data
     *
     * @return \MUtil_Model_ModelAbstract
     */
    public function getSourceModel()
    {
        return $this->sourceModel;
    }

    /**
     * The final directory when the data was successfully imported.
     *
     * If empty the file is thrown away after the import.
     *
     * @return string String or null when there is no long term storage
     */
    public function getSuccessDirectory()
    {
        return $this->successDirectory;
    }

    /**
     * Get the target model for the imported data
     *
     * @return \MUtil_Model_ModelAbstract
     */
    public function getTargetModel()
    {
        return $this->targetModel;
    }

    /**
     * The final directory for when the data could not be imported.
     *
     * If empty the file is thrown away after the failure.
     *
     * $param string $directory String or null when there is no failure storage
     * @return \MUtil_Model_Importer (continuation pattern)
     */
    public function setFailureDirectory($directory = null)
    {
        $this->failureDirectory = $directory;
        return $this;
    }

    /**
     * Set the current translator
     *
     * @param \MUtil_Model_ModelTranslatorInterface $translator
     * @return \MUtil_Model_Importer (continuation pattern)
     * @throws \MUtil_Model_ModelTranslateException for string translators that do not exist
     */
    public function setImportTranslator(\MUtil_Model_ModelTranslatorInterface $translator)
    {
        $this->importTranslator = $translator;

        if ($this->targetModel instanceof \MUtil_Model_ModelAbstract) {
            $this->importTranslator->setTargetModel($this->targetModel);
        }
        return $this;
    }

    /**
     * Set the data source for items created this importer
     *
     * @param \MUtil_Registry_SourceInterface $source
     * @return \MUtil_Model_Importer (continuation pattern)
     */
    public function setRegistrySource(\MUtil_Registry_SourceInterface $source)
    {
        $this->registrySource = $source;
        return $this;
    }

    /**
     * The filename minus the extension for long term storage.
     *
     * If empty the file is not renamed and may overwrite an existing file.
     *
     * @param string $noExtensionFilename String or null when the file is not renamed
     * @return \MUtil_Model_Importer (continuation pattern)
     */
    public function setLongtermFilename($noExtensionFilename = null)
    {
        $this->longtermFilename = $noExtensionFilename;
        return $this;
    }

    /**
     * Set the source model using a filename
     *
     * @param string $filename
     * @param string $extension Optional extension if the extension of the file should not be used
     * @return \MUtil_Model_Importer (continuation pattern)
     * @throws \MUtil_Model_ModelTranslateException for files with an unsupported extension or that fail to load
     */
    public function setSourceFile($filename, $extension = null)
    {
        if (null === $filename) {
            throw new \MUtil_Model_ModelTranslateException($this->_("No filename specified to import"));
        }

        if (null === $extension) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
        }

        if (!file_exists($filename)) {
            throw new \MUtil_Model_ModelTranslateException(sprintf(
                    $this->_("File '%s' does not exist. Import not possible."),
                    $filename
                    ));
        }

        switch (strtolower($extension)) {
            case 'txt':
                $model = new \MUtil_Model_TabbedTextModel($filename);
                break;

            case 'csv':
                $model = new \MUtil_Model_CsvModel($filename);
                break;

            case 'xml':
                $model = new \MUtil_Model_XmlModel($filename);
                break;

            default:
                throw new \MUtil_Model_ModelTranslateException(sprintf(
                        $this->_("Unsupported file extension: %s. Import not possible."),
                        $extension
                        ));
        }

        $this->_filename  = $filename;
        $this->_extension = $extension;

        $this->setSourceModel($model);

        return $this;
    }

    /**
     * Set the source model that provides the import data
     *
     * @param \MUtil_Model_ModelAbstract $model
     * @return \MUtil_Model_Importer (continuation pattern)
     */
    public function setSourceModel(\MUtil_Model_ModelAbstract $model)
    {
        $this->sourceModel = $model;
        return $this;
    }

    /**
     * The final directory when the data was successfully imported.
     *
     * If empty the file is thrown away after the import.
     *
     * $param string $directory String or null when there is no long term storage
     * @return \MUtil_Model_Importer (continuation pattern)
     */
    public function setSuccessDirectory($directory = null)
    {
        $this->successDirectory = $directory;
        return $this;
    }

    /**
     * Set the target model for the imported data
     *
     * @param \MUtil_Model_ModelAbstract $model
     * @return \MUtil_Model_Importer (continuation pattern)
     */
    public function setTargetModel(\MUtil_Model_ModelAbstract $model)
    {
        $this->targetModel = $model;

        if ($this->importTranslator instanceof \MUtil_Model_ModelTranslatorInterface) {
            $this->importTranslator->setTargetModel($model);
        }

        return $this;
    }
}
