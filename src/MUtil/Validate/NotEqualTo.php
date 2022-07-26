<?php

namespace MUtil\Validate;

use ArrayAccess;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\InvalidArgumentException;


/**
 * Validates the a value is not the same as some other field value
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.4 17-okt-2014 15:23:24
 */
class NotEqualTo extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    public const NOT_EQUAL_TO = 'notEqualTo';

    protected $messageTemplates = [
        self::NOT_EQUAL_TO => "Values may not be the same.",
    ];

    /**
     * The field names against which to validate
     *
     * @var array
     */
    protected $fields;

    /**
     * An array containing field field specific error messages
     *
     * @var array fieldName => $message
     */
    protected $fieldMessages;

    /**
     * Sets validator options
     *
     * @param array|string $fields On or more values that this element should not have
     * @param string|array Optional different message or an array of messages containing field names, an int array value is set as a general message
     */
    public function __construct($fields, $message = null)
    {
        parent::__construct();

        $this->fields = (array) $fields;

        if ($message) {
            foreach ((array) $message as $key => $msg) {
                if (in_array($key, $this->fields, true)) {
                    $this->fieldMessages[$key] = $msg;
                } else {
                    $this->setMessage($msg, self::NOT_EQUAL_TO);
                }
            }
        }


    }

    /**
     * Defined by \Zend_Validate_Interface
     *
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value, $context = [])
    {
        if ($value) {
            foreach ($this->fields as $field) {
                if (isset($context[$field]) && ($value == $context[$field])) {

                    if (isset($this->fieldMessages[$field])) {
                        $this->setMessage($this->fieldMessages[$field], self::NOT_EQUAL_TO);
                    }

                    $this->setValue((string) $value);
                    $this->error(self::NOT_EQUAL_TO);
                    return false;
                }
            }
        }

        return true;
    }
}
