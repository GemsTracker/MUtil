<?php

namespace MUtil\Validate\File;

use MUtil\Validate\Regexclude;

/**
 * Make sure the value does not start at the root of the fiel sustem
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class IsRelativePath extends Regexclude
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "Invalid type given. String, integer or float expected",
        self::MATCH     => "Only relative paths are allowed",
        self::ERROROUS  => "There was an internal error while using the pattern '%pattern%'",
    );

    /**
     * Regular expression pattern: should not start with "[letter]:", "\" or "/"
     *
     * @var string
     */
    protected string $pattern = '#^([a-zA-Z]:|\\\\|/)#';
}
