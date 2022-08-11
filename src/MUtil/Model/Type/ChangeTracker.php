<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Type
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Type;

use \DateTimeImmutable;
use \DateTimeInterface;

/**
 * A type that allows you to check after the save() if a field value was changed.
 *
 * After applying this you need to add all fields in $model->getMeta(\MUtil\Model\Type\ChangeTracker::HIDDEN_FIELDS)
 * as hidden fields to the form.
 *
 * @package    MUtil
 * @subpackage Model_Type
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.4
 */
class ChangeTracker
{
    /**
     * Constant for model META tag
     */
    const HIDDEN_FIELDS = '__extra_hidden_fields';

    /**
     * Prefix for automatically genereating the field that contains the old value
     */
    const OLD_FIELD_PREFIX = 'original_field__';

    /**
     * The value to store when the tracked field has changed
     *
     * @var mixed
     */
    private $_changedValue;

    /**
     *
     * @var \MUtil\Model\ModelAbstract
     */
    private $_model;

    /**
     * The value to store when the tracked field has not changed
     *
     * @var mixed
     */
    private $_unchangedValue;

    /**
     *
     * @param \MUtil\Model\ModelAbstract $model
     * @param mixed $changedValue The value to store when the tracked field has changed
     * @param mixed $unchangedValue The value to store when the tracked field did not change
     */
    public function __construct(\MUtil\Model\ModelAbstract $model, $changedValue = true, $unchangedValue = false)
    {
        $this->_model          = $model;
        $this->_changedValue   = $changedValue;
        $this->_unchangedValue = $unchangedValue;
    }

    /**
     *
     * @param string $trackingField The field that stores the tracking value
     * @param string $trackedField   The field whose changing is tracked
     * @param string $oldValueField Optional, the field to store a copy of the original value in
     */
    public function apply($trackingField, $trackedField, $oldValueField = null)
    {
        if (null === $oldValueField) {
            // Defautl name
            $oldValueField = self::OLD_FIELD_PREFIX . $trackedField;

            // Check if a fields already existed
            if (method_exists($this->_model, 'getKeyCopyName')) {
                $copyName = $this->_model->getKeyCopyName($trackedField);

                if ($this->_model->has($copyName)) {
                    $oldValueField = $copyName;
                }
            }
        }

        if (! $this->_model->has($oldValueField)) {
            $this->_model->set($oldValueField, 'elementClass', 'Hidden', __CLASS__, $trackedField);
            $this->_model->setOnLoad($oldValueField, array($this, 'loadOldValue'));
        }

        if (! $this->_model->has($trackingField)) {
            // Only load the original value and ste the original result when it was not already in the model
            $this->_model->setAutoSave($trackingField);
            // $this->_model->setOnLoad($trackingField, $this->_unchangedValue);
        }
        $this->_model->set($trackingField, __CLASS__, array($trackedField, $oldValueField));
        $this->_model->setOnSave($trackingField, array($this, 'saveValue'));

        // Store the extra hidden fields needed
        $hiddenFields = $this->_model->getMeta(self::HIDDEN_FIELDS, array());
        $hiddenFields[] = $oldValueField;
        $this->_model->setMeta(self::HIDDEN_FIELDS, $hiddenFields);

        // Make sure the fields are in the result set
        $this->_model->get($trackedField);
        $this->_model->get($trackingField);
        $this->_model->get($oldValueField);
    }

    /**
     * A ModelAbstract->setOnLoad() function that copies the value from the original
     *
     * @see \MUtil\Model\ModelAbstract
     *
     * @param mixed $value The value being saved
     * @param boolean $isNew True when a new item is being saved
     * @param string $name The name of the current field
     * @param array $context Optional, the other values being saved
     * @param boolean $isPost True when passing on post data
     * @return array Of the values
     */
    public function loadOldValue($value, $isNew = false, $name = null, array $context = array(), $isPost = false)
    {
        if ($isPost) {
            return $value;
        }

        $trackedField = $this->_model->get($name, __CLASS__);

        if (isset($context[$trackedField])) {
            $value = $context[$trackedField];
        }

        return $value;
    }
    /**
     * A ModelAbstract->setOnSave() function that tracks the change
     *
     * @see \MUtil\Model\ModelAbstract
     *
     * @param mixed $value The value being saved
     * @param boolean $isNew True when a new item is being saved
     * @param string $name The name of the current field
     * @param array $context Optional, the other values being saved
     * @return string Of the values concatenated
     */
    public function saveValue($value, $isNew = false, $name = null, array $context = array())
    {
        // \MUtil\EchoOut\EchoOut::track($value, $this->_changedValue);

        // Once the value is set (and e.g. stored in the database) do not overwrite it
        if ($this->_changedValue == $value) {
            return $value;
        }

        $compare = $this->_model->get($name, __CLASS__);
        if (! (is_array($compare) && (2 === count($compare)))) {
            // Actually a valid setting, do nothring
            return $value;
        }

        list($trackedField, $oldValueField) = $compare;

        if (! isset($context[$trackedField], $context[$oldValueField])) {
            return $value;
        }

        if (! ($context[$trackedField] && $context[$oldValueField])) {
            return $context[$trackedField] || $context[$oldValueField] ? $this->_changedValue : $this->_unchangedValue;
        }

        $storageFormat = $this->_model->get($trackedField, 'storageFormat');

        if (! $storageFormat) {
            return $context[$trackedField] == $context[$oldValueField] ? $this->_unchangedValue : $this->_changedValue;
        }

        if ($context[$oldValueField] instanceof DateTimeInterface) {
            $oldValue = $context[$oldValueField];
        } else {
            $oldValue = DateTimeImmutable::createFromFormat($storageFormat, $context[$oldValueField]);
        }

        if ($context[$trackedField] instanceof DateTimeInterface) {
            $currentValue = $context[$trackedField];
        } else {
            $currentValue = DateTimeImmutable::createFromFormat($storageFormat, $context[$trackedField]);
            if (! $currentValue) {
                if ($this->_model->has($trackedField, 'dateFormat')) {
                    $secondFormat = $this->_model->get($trackedField, 'dateFormat');
                    $currentValue = DateTimeImmutable::createFromFormat($secondFormat, $context[$trackedField]);
                } else {
                    return flase;
                }
            }
        }

        // \MUtil\EchoOut\EchoOut::track($trackedField, $oldValueField, $oldValue->toString(), $currentValue->toString(), $oldValue->getTimestamp() === $currentValue->getTimestamp());

        return $oldValue->getTimestamp() === $currentValue->getTimestamp() ?
                $this->_unchangedValue :
                $this->_changedValue;
    }
}
