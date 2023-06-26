<?php

namespace MUtil\Validator;
/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.2 Feb 7, 2017 5:03:48 PM
 */
class NoTags extends Regexclude
{
    protected const NOTAGS_REGEX = '/&(?:[a-z\d]+|#\d+|#x[a-f\d]+);|[<][a-z\\\\\/:]/i';

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected string $pattern = self::NOTAGS_REGEX;

    /**
     * Sets validator options
     *
     * @param  string $pattern
     * @return void
     */
    public function __construct(string $pattern = self::NOTAGS_REGEX)
    {
        $this->messageTemplates[parent::MATCH] = "No letters, ':' or '\\' are allowed directly after a '<' or '&' character.";
        parent::__construct($pattern);
    }

    /**
     * Defined by ValidatorInterface
     *
     * Returns true if and only if $value matches against the pattern option
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value): bool
    {
        if ((null === $value) || ('' == $value) || (is_array($value) && empty($value)) || is_object($value)) {
            return true;
        }

        if (is_array($value)) {
            $result = true;

            foreach ($value as $v) {
                $result = $this->isValid($v) && $result;
            }
            
            return $result;
        }

        return parent::isValid($value);
    }
}
