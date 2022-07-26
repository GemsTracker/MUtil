<?php

namespace MUtil\Validate;

use Laminas\Validator\Regex;

/**
 *
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Validate that the input is a phone number
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class Phone extends Regex
{
    const PHONE_REGEX = '/^[\d\s\+\(\)\-]*$/';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID   => "Invalid type given, value should be string, integer or float",
        self::NOT_MATCH => "'%value%' is not a phone number (e.g. +12 (0)34-567 890).",
    ];

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $pattern = self::PHONE_REGEX;

    /**
     * Sets validator options
     *
     * @param  string $pattern
     * @return void
     */
    public function __construct(string $pattern = self::PHONE_REGEX)
    {
        parent::__construct($pattern);
    }
}