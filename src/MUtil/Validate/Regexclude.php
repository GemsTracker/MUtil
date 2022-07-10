<?php

namespace MUtil\Validate;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\RuntimeException;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

/**
 * Negative Regex validator: the regular expression should not match!
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.3
 */
class Regexclude extends AbstractValidator
{
    const INVALID   = 'regexInvalid';
    const MATCH     = 'regexMatch';
    const ERROROUS  = 'regexErrorous';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID   => "Invalid type given. String, integer or float expected",
        self::MATCH     => "'%value%' does match against pattern '%pattern%'",
        self::ERROROUS  => "There was an internal error while using the pattern '%pattern%'",
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'pattern' => 'pattern'
    ];

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected string $pattern;

    /**
     * Sets validator options
     *
     * @param  string regex parern $pattern
     * @throws \Zend_Validate_Exception On missing 'pattern' parameter
     * @return void
     */
    public function __construct(?string $pattern = null)
    {
        parent::__construct();
        if ($this->pattern && !$pattern) {
            return;
        }

        $this->setPattern($pattern);
    }

    /**
     * Returns the pattern option
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Sets the pattern option
     *
     * @param  string $pattern
     * @throws RuntimeException if there is a fatal error in pattern matching
     * @return \Zend_Validate_Regex Provides a fluent interface
     */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;
        $status         = @preg_match($this->pattern, "Test");

        if (false === $status) {
            throw new RuntimeException("Internal error while using the pattern '$this->pattern'");
        }

        return $this;
    }

    /**
     * Defined by \Zend_Validate_Interface
     *
     * Returns true if and only if $value matches against the pattern option
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid(mixed $value): bool
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        $status = @preg_match($this->pattern, $value);
        if (false === $status) {
            $this->error(self::ERROROUS);
            return false;
        }

        if ($status) {
            $this->error(self::MATCH);
            return false;
        }

        return true;
    }
}
