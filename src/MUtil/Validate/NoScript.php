<?php

namespace MUtil\Validate;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Validate that the input is not an attempt to put any XSS text in the input.
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.0
 */
class NoScript extends Regexclude
{
    const SCRIPT_REGEX = '/[<>{}\(\)]/';

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected string $pattern = self::SCRIPT_REGEX;

    /**
     * Sets validator options
     *
     * @param  string $pattern
     * @return void
     */
    public function __construct(string $pattern = self::SCRIPT_REGEX)
    {
        $this->messageTemplates[parent::MATCH] = "Html tags may not be entered here.";
        parent::__construct($pattern);


    }
}