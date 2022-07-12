<?php

namespace MUtil\Validate;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Regex;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class SimpleEmail extends AbstractValidator
{
    public const INVALID   = 'emailInvalid';
    public const NOT_MATCH = 'emailNotMatch';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID   => "Invalid type given, value should be string, integer or float",
        self::NOT_MATCH => "'%value%' is not an email address (e.g. name@somewhere.com).",
    ];

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value matches against the pattern option
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value): bool
    {
        $this->setValue($value);

        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $status = filter_var($value, FILTER_VALIDATE_EMAIL);
        if (false === $status) {
            $this->error(self::NOT_MATCH);
            return false;
        }

        return true;
    }
}