<?php

namespace MUtil\Validator\Db;

use Laminas\Validator\ValidatorInterface;

/**
 * Unique database validation with provision for the value not being changed
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class ZendDbUniqueValue extends \Zend_Validate_Db_NoRecordExists implements ValidatorInterface
{
    protected $_checkFields;
    protected $_keyFields;
    protected $_postName;

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(
        self::ERROR_NO_RECORD_FOUND => 'No record matching %value% was found.',
        self::ERROR_RECORD_FOUND    => 'A duplicate record matching \'%value%\' was found.',
    );


    /**
     * Provides basic configuration for use with \Zend_Validate_Db Validators
     * Setting $exclude allows a single record to be excluded from matching.
     * The KeyFields are fields that occur as names in the context of the form and that
     * identify the current row - that can have the value.
     * A database adapter may optionally be supplied to avoid using the registered default adapter.
     *
     * @param string|array $table The database table to validate against, or array with table and schema keys
     * @param string|array $field A field to check or an array of fields to check for an
     * unique value combination, though only the value of the first will be shown
     * @param string|array $keyFields Names of the key fields to filter out the row of the value
     * @param \Zend_Db_Adapter_Abstract $adapter An optional database adapter to use.
     */
    public function __construct($table, $field, $keyFields, \Zend_Db_Adapter_Abstract $adapter = null)
    {
        if (is_array($field)) {
            // This means a COMBINATION of fields must be unique
            foreach ($field as $dbField => $postVar) {
                if (is_int($dbField)) {
                    $this->_checkFields[$postVar] = $postVar;
                } else {
                    $this->_checkFields[$dbField] = $postVar;
                }
            }

            // Remove the first field from array, it is used as the "one" field
            // of the parent.
            $this->_postName = reset($this->_checkFields);
            $field = key($this->_checkFields);
            array_shift($this->_checkFields);
        } else {
            $this->_postName = $field;
        }

        parent::__construct($table, $field, null, $adapter);

        if (is_array($keyFields)) {
            foreach ($keyFields as $dbField => $postVar) {
                if (is_int($dbField)) {
                    $this->_keyFields[$postVar] = $postVar;
                } else {
                    $this->_keyFields[$dbField] = $postVar;
                }
            }
        } elseif ($keyFields) {
            $this->_keyFields = array($keyFields => $keyFields);
        }
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     * @throws \Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value, $context = array())
    {
        /**
         * Check for an adapter being defined. if not, fetch the default adapter.
         */
        if ($this->_adapter === null) {
            $this->_adapter = \Zend_Db_Table_Abstract::getDefaultAdapter();
            if (null === $this->_adapter) {
                require_once 'Zend/Validate/Exception.php';
                throw new \Zend_Validate_Exception('No database adapter present');
            }
        }

        if ($this->_postName && isset($context[$this->_postName])) {
            $context[$this->_postName] = $value;
        }

        $includes = array();
        if ($this->_checkFields) {
            foreach ($this->_checkFields as $dbField => $postVar) {
                if (isset($context[$postVar]) && strlen($context[$postVar])) {
                    $condition  = $this->_adapter->quoteIdentifier($dbField) . ' = ?';
                    $includes[] = $this->_adapter->quoteInto($condition, $context[$postVar]);
                } else {
                    $includes[] = $this->_adapter->quoteIdentifier($dbField) . ' IS NULL';
                }
            }

        } else {
            // Quick check, only one _keyFields element
            if ($this->_keyFields && (count($this->_keyFields) == 1)) {
                $postVar = reset($this->_keyFields);
                $dbField = key($this->_keyFields);

                // _keyFields is the same as data field and value is set
                if (($dbField == $this->_field) && isset($context[$postVar]) && strlen($context[$postVar])) {
                    // The if the content is identical, we known this check to return
                    // true. No need to check the database.
                    if ($value == $context[$postVar]) {
                        return true;
                    }
                }
            }
        }

        $excludes = array();
        if ($this->_keyFields) {
            foreach ($this->_keyFields as $dbField => $postVar) {
                if (isset($context[$postVar]) && strlen($context[$postVar])) {
                    $condition  = $this->_adapter->quoteIdentifier($dbField) . ' = ?';
                    $excludes[] = $this->_adapter->quoteInto($condition, $context[$postVar]);
                } else {
                    // If one of the key values is empty, do not check for the combination
                    // (i.e. probably this is an insert, but in any case, no check).
                    $excludes = array();
                    break;
                }
            }
        }

        if ($includes || $excludes) {
            if ($includes) {
                $this->_exclude = implode(' AND ', $includes);

                if ($excludes) {
                    $this->_exclude .= ' AND ';
                }
            } else {
                // Clear cached query
                $this->_exclude = '';
            }
            if ($excludes) {
                $this->_exclude .= 'NOT (' . implode(' AND ', $excludes) . ')';
            }
        } else {
            $this->_exclude = null;
        }
        // Clear cached query
        $this->_select = null;

        // \MUtil\EchoOut\EchoOut::track($this->_exclude, $this->_checkFields, $this->_keyFields, $context, $_POST);

        return parent::isValid($value, $context);
    }
}
