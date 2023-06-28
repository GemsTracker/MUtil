<?php

namespace MUtil\Validator;

use Laminas\Validator\AbstractValidator;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class IsConfirmed extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    public const NOT_SAME           = 'notSame';
    public const MISSING_DATA       = 'missingData';
    public const MISSING_FIELD_NAME = 'missingFieldName';

    protected array $messageTemplates = [
        self::NOT_SAME              => "Must be the same as %fieldDescription%.",
        self::MISSING_DATA          => "Field %fieldName% missing in data",
        self::MISSING_FIELD_NAME    => 'No field was provided to match against.',
    ];

    /**
     * @var array
     */
    protected array $messageVariables = [
        'fieldName' => 'fieldName',
        'fieldDescription' => 'fieldDescription',
    ];

    /**
     * The field name against which to validate
     * @var string
     */
    protected ?string $fieldName = null;

    /**
     * Description of field name against which to validate
     * @var string
     */
    protected ?string $fieldDescription = null;

    /**
     * Sets validator options
     *
     * @param  string $fieldName  Field name against which to validate
     * $param string $fieldDescription  Description of field name against which to validate
     * @return void
     */
    public function __construct(?string $fieldName = null, ?string $fieldDescription = null)
    {
        parent::__construct();
        if (null !== $fieldDescription) {
            $this->setFieldDescription($fieldDescription);
        }
        if (null !== $fieldName) {
            $this->setFieldName($fieldName);
        }
    }

    /**
     * Get field name against which to compare
     *
     * @return String
     */
    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    /**
     * Get field name against which to compare
     *
     * @return String
     */
    public function getFieldDescription(): ?string
    {
        return $this->fieldDescription;
    }

    /**
     * Defined by ValidatorInterface
     *
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value, array $context = []): bool
    {
        $this->setValue((string) $value);
        $fieldName = $this->getFieldName();

        if ($fieldName === null) {
            $this->error(self::MISSING_FIELD_NAME);
            return false;
        }

        if (!array_key_exists($fieldName, $context)) {
            $this->error(self::MISSING_DATA);
            return false;
        }

        if ($value !== $context[$fieldName])  {
            $this->error(self::NOT_SAME);
            return false;
        }

        return true;
    }

    /**
     * Set field name against which to compare
     *
     * @param  mixed $token
     * @return self
     */
    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;

        if (! $this->fieldDescription) {
            $this->setFieldDescription($fieldName);
        }

        return $this;
    }

    /**
     * Set field name against which to compare
     *
     * @param  mixed $description
     * @return self
     */
    public function setFieldDescription($description): self
    {
        $this->fieldDescription = $description;
        return $this;
    }
}
