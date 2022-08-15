<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Log
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil;

/**
 *
 *
 * @package    MUtil
 * @subpackage Log
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.2
 */
class Log extends \Zend_Log
{
    /**
     * Have a log file for each month, overwriting it after 11 months
     */
    const ROTATE_PER_MONTH = 'M';

    /**
     * Have a log file for each month, overwriting it after 51 weeks
     */
    const ROTATE_PER_WEEK = 'W';

    /**
     * The current filename
     *
     * @var string
     */
    protected $_logFileName;

    /**
     * The root for the current filenamea
     *
     * @var string
     */
    protected $_logFileRoot;

    /**
     * The rotate function
     *
     * @var string
     */
    protected $_logRotate;

    /**
     * Construct a logger with filename depending on $logRotate
     *
     * @param mixed $filename Start of the filename minus .log extension
     * @param mixed $logRotate One of the cosntants for log rotate
     * @param int One of the \Zend_Log constants
     */
    public function __construct($filename, $logRotate = null, $priority = null)
    {
        $this->_logFileRoot = $filename;
        $this->_logRotate   = $logRotate;
        $this->_logFileName = $this->_getLogFileName();

        // Empty the file if it is on rotation after a year
        // $this->_checkLogOverwrite(); NEVER IMPLEMENTED

        try {
            $writer = new \Zend_Log_Writer_Stream($this->_logFileName);
        } catch (\Exception $exc) {
            try {
                // Try to solve the problem, otherwise fail heroically
                \MUtil\File::ensureDir(dirname($this->_logFileName));
                $writer = new \Zend_Log_Writer_Stream($this->_logFileName);
            } catch (\Exception $exc) {
                $this->bootstrap(array('locale', 'translate'));
                die(sprintf($this->translate->_('Path %s not writable'), dirname($this->_logFileName)));
            }
        }

        parent::__construct($writer);

        if (null !== $priority) {
            $this->setLogPriority($priority);
        }
    }

    /**
     * Get the filename to use
     *
     * @param int $index
     * @return string
     */
    protected function _getLogFileName($index = null)
    {
        switch ($this->_logRotate) {
            case self::ROTATE_PER_MONTH:
                if (null === $index) {
                    $now = new \DateTimeImmutable();
                    $index = $now->format('M');
                } elseif ($index < 10) {
                    $index = "0" . $index;
                }
                $filename = $this->_logFileRoot . '-mon-' . $index . '.log';
                break;

            case self::ROTATE_PER_MONTH:
                if (null === $index) {
                    $now = new \DateTimeImmutable();
                    $index = $now->format('W');
                } elseif ($index < 10) {
                    $index = "0" . $index;
                }
                $filename = $this->_logFileRoot . '-wk-' . $index . '.log';
                break;

            default:
                $filename = $this->_logFileRoot;
                if (! \MUtil\StringUtil\StringUtil::endsWith($filename, '.log')) {
                    $filename .= '.log';
                }
                break;
        }

        return $filename;
    }

    /**
     * Clear the error log
     *
     * @param $index
     * @return boolean True if the file no longer exists
     */
    public function clearLogFile($index = null)
    {
        if (file_exists($this->_logFileName)) {
            $writer = reset($this->_writers);
            if ($writer instanceof \Zend_Log_Writer_Stream) {
                // $writer->
            }

            return unlink($this->_logFileName);
        }

        return true;
    }

    /**
     * Get the log file content
     *
     * @return string
     */
    public function getLogFileContent()
    {
        if (file_exists($this->_logFileName)) {
            return file_get_contents($this->_logFileName);
        }

        return null;
    }

    /**
     * Get the log file name
     *
     * @return string
     */
    public function getLogFileName()
    {
        return $this->_logFileName;
    }

    public function getLogIndices()
    {
        $locale = null;
        if (\Zend_Registry::isRegistered('Zend_Locale')) {
            $locale = \Zend_Registry::get('Zend_Locale');
        } elseif (\Zend_Registry::isRegistered('locale')) {
            $locale = \Zend_Registry::get('locale');
        }
        if (! $locale instanceof \Zend_Locale) {
            $locale = \Zend_Locale::findLocale();
        }

        switch ($this->_logRotate) {
            case self::ROTATE_PER_MONTH:
                break;
        }
    }

    /**
     * Does the error log file exist
     *
     * @return boolean
     */
    public function hasLogFile()
    {
        return file_exists($this->_logFileName) && (filesize($this->_logFileName) > 0);
    }

    /**
     * Set the log level
     * @param int $priority One
     * @param int One of the \Zend_Log constants
     */
    public function setLogPriority($priority)
    {
        $writer = reset($this->_writers);

        if ($writer instanceof \Zend_Log_Writer_Abstract) {
            $filter = new \Zend_Log_Filter_Priority($priority);
            $writer->addFilter($filter);
        }

        return $this;
    }
}
