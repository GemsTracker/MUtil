<?php

namespace MUtil\Validate\Model;

use Laminas\Validator\AbstractValidator;

/**
 *
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.4 23-jan-2014 17:57:39
 */
class UniqueValue extends AbstractValidator
{
    /**
     * Error constants
     */
    public const ERROR_RECORD_FOUND    = 'recordFound';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = [
        self::ERROR_RECORD_FOUND    => 'A duplicate record matching \'%value%\' was found.',
    ];

    /**
     *
     * @var array
     */
    protected array $fields;

    /**
     *
     * @var \MUtil_Model_ModelAbstract
     */
    protected \MUtil_Model_ModelAbstract $model;

    /**
     *
     * @param \MUtil_Model_ModelAbstract $model
     * @param string|array $field A field to check or an array of fields to check for an
     * unique value combination, though only the value of the first will be shown
     */
    public function __construct(\MUtil_Model_ModelAbstract $model, string|array $field)
    {
        parent::__construct();
        $this->model  = $model;
        $this->fields = (array) $field;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = array())
    {
        $this->setValue($value);

        // Make sure the (optionally filtered) value is in the context
        $context[reset($this->fields)] = $value;

        $filter = array();
        foreach ($this->fields as $name) {
            // Return valid when not all the fields to check for are in the context
            if (! isset($context[$name])) {
                return true;
            }

            $filter[$name] = $context[$name];
        }

        $check = array();
        $doGet = $this->model->hasItemsUsed();
        $keys  = $this->model->getKeys();
        foreach ($keys as $id => $key) {
            if ($doGet) {
                // Make sure the item is used
                $this->model->get($key);
            }
            if (isset($context[$id])) {
                $check[$key] = $context[$id];
            } elseif (isset($context[$key])) {
                $check[$key] = $context[$key];
            } else {
                // Not all keys are in => therefore this is a new item
                $check = false;
                break;
            }
        }

        $rows = $this->model->load($filter);

        if (! $rows) {
            return true;
        }

        if (! $check) {
            // Rows where found while it is a new item
            $this->error(self::ERROR_RECORD_FOUND);
            return false;
        }

        $count = count($check);
        foreach ($rows as $row) {
            // Check for return of the whole check
            if (count(array_intersect_assoc($check, $row)) !== $count) {
                // There exists a row with the same values but not the same keys
                $this->error(self::ERROR_RECORD_FOUND);
                return false;
            }
        }

        return true;
    }
}
