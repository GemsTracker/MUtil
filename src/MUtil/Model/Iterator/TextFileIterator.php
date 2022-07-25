<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Iterator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Iterator;

/**
 * Iterate line by line through a file, with a separate output for the first header line
 *
 * @package    MUtil
 * @subpackage Model_Iterator
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class TextFileIterator implements \Countable, \Iterator
{
    /**
     * @var int
     */
    protected $_count = null;
    
    /**
     *
     * @var array
     */
    protected $_fieldMap;

    /**
     * Count of the fieldmap
     *
     * @var int
     */
    protected $_fieldMapCount;

    /**
     *
     * @var \SplFileObject
     */
    protected $_file = null;

    /**
     * The name of the content file
     *
     * @var string
     */
    protected $_filename;

    /**
     * The position of the current item in the file
     *
     * @var int
     */
    protected $_filepos = null;

    /**
     * The current key value
     *
     * @var type
     */
    protected $_key = 0;

    /**
     * The function that splits the input string into an array
     *
     * @var callable
     */
    protected $_splitFunction;

    /**
     *
     * @var boolean
     */
    protected $_valid = true;

    /**
     * Initiate this line by line file iterator
     *
     * @param string $filename
     * @param callable $splitFunction function(string currentLine) => row array. Used on first line to get mapping
     */
    public function __construct($filename, $splitFunction)
    {
        $this->_filename      = $filename;
        $this->_splitFunction = $splitFunction;

        if (!is_callable($splitFunction)) {
            throw new \MUtil\Model\ModelException(__CLASS__ . " needs a callable splitFunction argument.");
        }
    }

    /**
     *
     * @return boolean
     */
    private function _accept()
    {
        return (boolean) trim($this->_file->current(), "\r\n");
    }

    /**
     * Open the file and optionally restore the position
     *
     * @return void
     */
    private function _openFile()
    {
        $this->_fieldMap      = array();
        $this->_fieldMapCount = 0;

        if (! file_exists($this->_filename)) {
            $this->_file = false;
            return;
        }

        try {
            $this->_file = new \SplFileObject($this->_filename, 'r');
            $firstline   = trim(\MUtil\Encoding::removeBOM($this->_file->current(), "\r\n"));

            if ($firstline) {
                $this->_fieldMap = call_user_func($this->_splitFunction, $firstline);
                $this->_fieldMapCount = count($this->_fieldMap);

                // Check for fields, do not run when empty
                if (0 === $this->_fieldMapCount) {
                    $this->_file = false;
                    return;
                }
            }

            // Restore old file position if any
            if (null !== $this->_filepos) {
                $this->_file->fseek($this->_filepos, SEEK_SET);
            }

            // Always move to next, even if there was no first line
            $this->next();

        } catch (\Exception $e) {
            $this->_file = false;
        }
    }
    
    /**
     * Return the number of records in the file
     * 
     * @return int
     */
    public function count(): int
    {
        if ($this->_count === null) {
            // Save position like in serialize
            $key = $this->key() - 1;
            $filepos = $this->_filepos;
            
            $this->rewind();
            $this->_count = 0;
            foreach($this as $row)
            {
                $this->_count++;                
            }
            
            // Now restore position
            $this->_key = $key;
            $this->_filepos = $filepos;
            $this->_openFile();         
        }
        
        return $this->_count;        
    }

    /**
     * Return the current element
     *
     * @return array or false
     */
    public function current(): mixed
    {
        if (null === $this->_file) {
            $this->_openFile();
        }

        if (! ($this->_file instanceof \SplFileObject && $this->_valid)) {
            return false;
        }

        $fields     = call_user_func($this->_splitFunction, trim($this->_file->current(), "\r\n"));
        $fieldCount = count($fields);

        if (0 ===  $fieldCount) {
            return false;
        }

        if ($fieldCount > $this->_fieldMapCount) {
            // Remove extra fields from the input
            $fields = array_slice($fields, 0, $this->_fieldMapCount);

        } elseif ($fieldCount < $this->_fieldMapCount) {
            // Add extra nulls to the input
            $fields = $fields + array_fill($fieldCount, $this->_fieldMapCount - $fieldCount, null);
        }

        return array_combine($this->_fieldMap, $fields);
    }

    /**
     * Get the map array key value => field name to use
     *
     * This line can then be used to determined the mapping used by the mapping function.
     *
     * @return string Or boolean if file does not exist
     */
    public function getFieldMap()
    {
        if (null === $this->_file) {
            $this->_openFile();
        }

        return $this->_fieldMap;
    }

    /**
     * Return the key of the current element
     *
     * @return int
     */
    public function key(): mixed
    {
        if (null === $this->_file) {
            $this->_openFile();
        }

        return $this->_key;
    }

    /**
     * Move forward to next element
     */
    public function next(): void
    {
        if (null === $this->_file) {
            $this->_openFile();
        }

        if ($this->_file) {
            $this->_key = $this->_key + 1;
            while ($this->_file->valid()) {
                $this->_file->next();
                $this->_filepos = $this->_file->ftell();
                if ($this->_accept()) {
                    $this->_valid = true;
                    return;
                }
            }
        }
        $this->_valid = false;
    }

    /**
     *  Rewind the \Iterator to the first element
     */
    public function rewind(): void
    {
        $this->_filepos = null;
        $this->_key = 0;

        if (null === $this->_file) {
            $this->_openFile();
        } elseif ($this->_file) {
            $this->_file->rewind();
            $this->_file->current(); // Reading line is nexessary for correct loading of file.
            $this->next();
        }
    }

    /**
     * Return the string representation of the object.
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'filename' => $this->_filename,
            'filepos'  => $this->_filepos,
            'key'      => $this->_key - 1,
            'splitter' => $this->_splitFunction,
        ];
    }

    /**
     * Called during unserialization of the object.
     *
     * @param string $serialized
     */
    public function __unserialize($data)
    {
        if ($data === false) {
            $lastErr = error_get_last();
            error_log($lastErr['message'] . "\n", 3, ini_get('error_log'));
            return;
        }

        // WARNING! WARNING! WARNING!
        //
        // Do not reopen the file in the unserialize statement
        // 1 - the file gets locked
        // 2 - if the file is deleted you cannot reopen your session.
        //
        // Normally this is not a problem, but when
        $this->_file          = null;
        $this->_filename      = $data['filename'];
        $this->_filepos       = $data['filepos'];
        $this->_key           = $data['key'];
        $this->_splitFunction = $data['splitter'];
    }

    /**
     * True if not EOF
     *
     * @return boolean
     */
    public function valid(): bool
    {
        if (null === $this->_file) {
            $this->_openFile();
        }

        return $this->_file && $this->_valid;
    }
}
