<?php

/**
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model;

/**
 * Utility object for importing data from one model to another
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class Importer extends \MUtil\Translate\TranslateableAbstract
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
     * @var \MUtil\Task\TaskBatch
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
     * @var \MUtil\Model\ModelTranslatorInterface
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
     * @var \MUtil\Registry\SourceInterface
     */
    protected $registrySource;

    /**
     * Model to read import
     *
     * @var \MUtil\Model\ModelAbstract
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
     * @var \MUtil\Model\ModelAbstract
     */
    protected $targetModel;

    /**
     * Set the variables 
     * @param \MUtil\Task\TaskBatch $batch
     */
    protected function addVariablesToBatch(\MUtil\Task\TaskBatch $batch)
    {
        $batch->getStack()->registerAllowedClass('\\MUtil\\Date');
        
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
     * @return \MUtil\Model\Importer (continuation pattern)
     */
    public function clearFailureDirectory()
    {
        return $this->setFailureDirectory();
    }

    /**
     * Clears the filename for long term storage. The file is not renamed and
     * may overwrite an existing file.
     *
     * @return \MUtil\Model\Importer (continuation pattern)
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
     * @return \MUtil\Model\Importer (continuation pattern)
     */
    public function clearSuccessDirectory()
    {
        return $this->setSuccessDirectory();
    }

    /**
     *
     * @param string $idPart End part for batch id
     * @param \MUtil\Task\TaskBatch $batch Optional batch with different source etc..
     * @return \MUtil\Task\TaskBatch
     */
    protected function getBasicImportBatch($idPart, \MUtil\Task\TaskBatch $batch = null)
    {
        if (null === $batch) {
            $batch = new \MUtil\Task\TaskBatch('check_' . basename($this->sourceModel->getName()) . '_' . $idPart);
            $this->registrySource->applySource($batch);
            $batch->setSource($this->registrySource);
        }

        $this->addVariablesToBatch($batch);

        return $batch;
    }


    /**
     *
     * @param \MUtil\Task\TaskBatch $batch Optional batch with different source etc..
     * @return \MUtil\Task\TaskBatch
     */
    public function getCheckAndImportBatch(\MUtil\Task\TaskBatch $batch = null)
    {
        $batch = $this->getBasicImportBatch(__FUNCTION__, $batch);

        if (! $batch->isLoaded()) {
            $batch->addTask('Import\\ImportCheckTask');
            if ($this->_filename) {
                $batch->addTask(
                        'File\\CopyFileWhenTask',
                        $this->_filename,
                        $this->getFailureDirectory() . DIRECTORY_SEPARATOR . $this->getLongtermFilename() . '.' . $this->_extension,
                        'import_errors',
                        1);
            }
            $batch->addTask('CheckCounterTask', 'import_errors', $this->_('Found %2$d import error(s). Import aborted.'));
            if ($this->_filename) {
                $batch->addTask(
                        'AddTask', // AddTask task as when all is OK this task should be added
                        'File\\CopyFileWhenTask',
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
     * @param \MUtil\Task\TaskBatch $chechkBatch Optional check batch with different source etc..
     * @param \MUtil\Task\TaskBatch $importBatch Optional import batch with different source etc..
     * @return \MUtil\Task\TaskBatch
     */
    public function getCheckWithImportBatches(\MUtil\Task\TaskBatch $checkBatch = null, \MUtil\Task\TaskBatch $importBatch = null)
    {
        $batch = $this->getBasicImportBatch(__FUNCTION__, $checkBatch);

        if (! $batch->isLoaded()) {
            $batch->addTask('Import\\ImportCheckTask');
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
     * @return \MUtil\Model\ModelTranslatorInterface or null
     */
    public function getImportTranslator()
    {
        return $this->importTranslator;
    }

    /**
     *
     * @param \MUtil\Task\TaskBatch $batch Optional batch with different source etc..
     * @return \MUtil\Task\TaskBatch
     */
    public function getImportOnlyBatch(\MUtil\Task\TaskBatch $batch = null)
    {
        if (! $this->_importBatch instanceof \MUtil\Task\TaskBatch) {
            $batch = new \MUtil\Task\TaskBatch(__CLASS__ . '_import_' . basename($this->sourceModel->getName()) . '_' . __FUNCTION__);

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
                        'File\\CopyFileWhenTask',
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
     * @return \MUtil\Registry\SourceInterface
     */
    public function getRegistrySource()
    {
        return $this->registrySource;
    }

    /**
     * Get the source model that provides the import data
     *
     * @return \MUtil\Model\ModelAbstract
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
     * @return \MUtil\Model\ModelAbstract
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
     * @return \MUtil\Model\Importer (continuation pattern)
     */
    public function setFailureDirectory($directory = null)
    {
        $this->failureDirectory = $directory;
        return $this;
    }

    /**
     * Set the current translator
     *
     * @param \MUtil\Model\ModelTranslatorInterface $translator
     * @return \MUtil\Model\Importer (continuation pattern)
     * @throws \MUtil\Model\ModelTranslateException for string translators that do not exist
     */
    public function setImportTranslator(\MUtil\Model\ModelTranslatorInterface $translator)
    {
        $this->importTranslator = $translator;

        if ($this->targetModel instanceof \MUtil\Model\ModelAbstract) {
            $this->importTranslator->setTargetModel($this->targetModel);
        }
        return $this;
    }

    /**
     * Set the data source for items created this importer
     *
     * @param \MUtil\Registry\SourceInterface $source
     * @return \MUtil\Model\Importer (continuation pattern)
     */
    public function setRegistrySource(\MUtil\Registry\SourceInterface $source)
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
     * @return \MUtil\Model\Importer (continuation pattern)
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
     * @return \MUtil\Model\Importer (continuation pattern)
     * @throws \MUtil\Model\ModelTranslateException for files with an unsupported extension or that fail to load
     */
    public function setSourceFile($filename, $extension = null)
    {
        if (null === $filename) {
            throw new \MUtil\Model\ModelTranslateException($this->_("No filename specified to import"));
        }

        if (null === $extension) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
        }

        if (!file_exists($filename)) {
            throw new \MUtil\Model\ModelTranslateException(sprintf(
                    $this->_("File '%s' does not exist. Import not possible."),
                    $filename
                    ));
        }

        switch (strtolower($extension)) {
            case 'txt':
                $model = new \MUtil\Model\TabbedTextModel($filename);
                break;

            case 'csv':
                $model = new \MUtil\Model\CsvModel($filename);
                break;

            case 'xml':
                $model = new \MUtil\Model\XmlModel($filename);
                break;

            default:
                throw new \MUtil\Model\ModelTranslateException(sprintf(
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
     * @param \MUtil\Model\ModelAbstract $model
     * @return \MUtil\Model\Importer (continuation pattern)
     */
    public function setSourceModel(\MUtil\Model\ModelAbstract $model)
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
     * @return \MUtil\Model\Importer (continuation pattern)
     */
    public function setSuccessDirectory($directory = null)
    {
        $this->successDirectory = $directory;
        return $this;
    }

    /**
     * Set the target model for the imported data
     *
     * @param \MUtil\Model\ModelAbstract $model
     * @return \MUtil\Model\Importer (continuation pattern)
     */
    public function setTargetModel(\MUtil\Model\ModelAbstract $model)
    {
        $this->targetModel = $model;

        if ($this->importTranslator instanceof \MUtil\Model\ModelTranslatorInterface) {
            $this->importTranslator->setTargetModel($model);
        }

        return $this;
    }
}
