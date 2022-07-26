<?php

namespace MUtil\Validate\File;

use MUtil\Validate\Regexclude;

/**
 * Validates only that a string is a valid path
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class Path extends Regexclude
{
    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID   => "Invalid type given. String, integer or float expected",
        self::MATCH     => "'%pattern%' characters are forbidden in a path",
        self::ERROROUS  => "There was an internal error while using the pattern '%pattern%'",
    ];

    /**
     * Regular expression pattern: a good filename should not contain these characters
     *
     * @var string
     */
    protected string $pattern = '#[:?*|"<>]#';

     /**
     * Defined by \Zend_Validate_Interface
     *
     * Returns true if and only if $value matches against the pattern option
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value): bool
    {
        // Remove leading disk letter and colon
        if (preg_match('/^[a-zA-Z]:/', $value)) {
            $value = substr($value, 2);
        }

        return parent::isValid($value);
    }
}
