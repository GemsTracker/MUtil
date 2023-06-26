<?php

namespace MUtil\Validator;
use Laminas\Validator\Regex;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2017, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2017, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.4 30-Jun-2018 20:03:32
 */
class Base32 extends Regex
{
    // Reg checked at Wikipedia, only | and ` chars are technically allowed in name and not in there fro security reasons.
    protected const BASE32_REGEX = '/[^:upper:234567=]/';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID   => "Invalid type given, value should be string, integer or float",
        self::NOT_MATCH => "'%value%' is not a base 32 string. Only A through Z, 2 to 7 and = at the end are allowed.",
    ];

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $pattern = self::BASE32_REGEX;

    /**
     * Sets validator options
     *
     * @param  string $pattern
     * @return void
     */
    public function __construct(string $pattern = self::BASE32_REGEX)
    {
        parent::__construct($pattern);
    }
}
