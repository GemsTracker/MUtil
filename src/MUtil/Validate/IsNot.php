<?php

namespace MUtil\Validate;

use Laminas\Validator\AbstractValidator;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.2
 */
class IsNot extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    public const NOT_ONE = 'notOne';

    protected array $messageTemplates = [
        self::NOT_ONE => "This value is not allowed.",
    ];

    /**
     * The field name against which to validate
     *
     * @var mixed
     */
    protected mixed $notAllowedValues;

    /**
     * Sets validator options
     *
     * @param array}string $values On or more values that this element should not have
     * @param string Optional different message
     */
    public function __construct(mixed $values, ?string $message = null)
    {
        parent::__construct();
        $this->notAllowedValues = (array) $values;

        if ($message) {
            $this->setMessage($message, self::NOT_ONE);
        }
    }

    /**
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid(mixed $value, array $context = [])
    {
        if (in_array($value, $this->notAllowedValues)) {
            $this->setValue((string) $value);
            $this->error(self::NOT_ONE);
            return false;
        }

        return true;
    }
}
