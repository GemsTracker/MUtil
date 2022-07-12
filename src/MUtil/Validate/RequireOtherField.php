<?php

namespace MUtil\Validate;

use Laminas\Validator\AbstractValidator;

/**
 * Use \MUtil_Validate_Require when another value is required before this value can be set.
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.0
 */
class RequireOtherField extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    public const REQUIRED  = '#_required_validator';

    protected array $messageTemplates = [
        self::REQUIRED => "To set '%description%' you have to set '%fieldDescription%'.",
    ];

    /**
     * @var array
     */
    protected array $messageVariables = [
        'description' => '_description',
        'fieldDescription' => '_fieldDescription'
    ];


    protected string $description;

    /**
     * The field name against which to validate
     * @var string
     */
    protected string $fieldName;

    /**
     * Description of field name against which to validate
     * @var string
     */
    protected string $fieldDescription;

    /**
     * Sets validator options
     *
     * @param string $description Description (label) of this element
     * @param string $fieldName  Field name against which to validate
     * @param string $fieldDescription  Description of field name against which to validate
     * @return void
     */
    public function __construct($description, $fieldName, $fieldDescription)
    {
        parent::__construct();
        $this->_description = $description;
        $this->_fieldName = $fieldName;
        $this->_fieldDescription = $fieldDescription;
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
    public function isValid(mixed $value, array $context = []): bool
    {
        $this->setValue((string) $value);

        if ($value) {
            $fieldSet = isset($context[$this->_fieldName]) && $context[$this->_fieldName];

            if (! $fieldSet)  {
                $this->error(self::REQUIRED);
                return false;
            }
        }
        return true;
    }
}

