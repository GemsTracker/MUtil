<?php

namespace MUtil\Form\Element;

use Laminas\Validator\ValidatorInterface;

trait LaminasElementValidatorTrait
{
    /**
     * Add validator to validation chain
     *
     * Note: will overwrite existing validators if they are of the same class.
     *
     * @param  string|ValidatorInterface $validator
     * @param  bool $breakChainOnFailure
     * @param  array $options
     * @return self
     * @throws \Zend_Form_Exception if invalid validator type
     */
    public function addValidator($validator, $breakChainOnFailure = false, $options = [])
    {
        if ($validator instanceof ValidatorInterface) {
            $name = get_class($validator);
        } elseif (is_string($validator)) {
            $name      = $validator;
            $validator = [
                'validator' => $validator,
                'breakChainOnFailure' => $breakChainOnFailure,
                'options'             => $options,
            ];
        } else {
            throw new \Zend_Form_Exception('Invalid validator provided to addValidator; must be string or \\Laminas\\Validator\\ValidatorInterface');
        }

        $this->_validators[$name] = $validator;

        return $this;
    }

    /**
     * Add multiple validators
     *
     * @param  array $validators
     * @return \Zend_Form_Element
     */
    public function addValidators(array $validators)
    {
        foreach ($validators as $validatorInfo) {
            if (is_string($validatorInfo)) {
                $this->addValidator($validatorInfo);
            } elseif ($validatorInfo instanceof ValidatorInterface) {
                $this->addValidator($validatorInfo);
            } elseif (is_array($validatorInfo)) {
                $argc                = count($validatorInfo);
                $breakChainOnFailure = false;
                $options             = [];
                if (isset($validatorInfo['validator'])) {
                    $validator = $validatorInfo['validator'];
                    if (isset($validatorInfo['breakChainOnFailure'])) {
                        $breakChainOnFailure = $validatorInfo['breakChainOnFailure'];
                    }
                    if (isset($validatorInfo['options'])) {
                        $options = $validatorInfo['options'];
                    }
                    $this->addValidator($validator, $breakChainOnFailure, $options);
                } else {
                    switch (true) {
                        case (0 == $argc):
                            break;
                        case (1 <= $argc):
                            $validator  = array_shift($validatorInfo);
                        case (2 <= $argc):
                            $breakChainOnFailure = array_shift($validatorInfo);
                        case (3 <= $argc):
                            $options = array_shift($validatorInfo);
                        default:
                            $this->addValidator($validator, $breakChainOnFailure, $options);
                            break;
                    }
                }
            } else {
                require_once 'Zend/Form/Exception.php';
                throw new \Zend_Form_Exception('Invalid validator passed to addValidators() ' . get_class($validatorInfo));
            }
        }

        return $this;
    }
    
    /**
     * Retrieve all validators
     *
     * @return array
     */
    public function getValidators()
    {
        $validators = [];
        foreach ($this->_validators as $key => $value) {
            if ($value instanceof ValidatorInterface) {
                $validators[$key] = $value;
                continue;
            }
            $validator = $this->_loadValidator($value);
            $validators[get_class($validator)] = $validator;
        }
        return $validators;
    }

    /**
     * Validate element value
     *
     * If a translation adapter is registered, any error messages will be
     * translated according to the current locale, using the given error code;
     * if no matching translation is found, the original message will be
     * utilized.
     *
     * Note: The *filtered* value is validated.
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        $value = $this->getValue();

        if ((('' === $value) || (null === $value))
            && !$this->isRequired()
            && $this->getAllowEmpty()
        ) {
            return true;
        }

        if ($this->isRequired()
            && $this->autoInsertNotEmptyValidator()
            && !$this->getValidator('NotEmpty'))
        {
            $validators = $this->getValidators();
            $notEmpty   = ['validator' => 'NotEmpty', 'breakChainOnFailure' => true];
            array_unshift($validators, $notEmpty);
            $this->setValidators($validators);
        }

        // Find the correct translator. Zend_Validate_Abstract::getDefaultTranslator()
        // will get either the static translator attached to Zend_Validate_Abstract
        // or the 'Zend_Translate' from Zend_Registry.
        if (\Zend_Validate_Abstract::hasDefaultTranslator() &&
            !\Zend_Form::hasDefaultTranslator())
        {
            $translator = \Zend_Validate_Abstract::getDefaultTranslator();
            if ($this->hasTranslator()) {
                // only pick up this element's translator if it was attached directly.
                $translator = $this->getTranslator();
            }
        } else {
            $translator = $this->getTranslator();
        }

        $this->_messages = [];
        $this->_errors   = [];
        $result          = true;
        $isArray         = $this->isArray();
        foreach ($this->getValidators() as $key => $validator) {
            if (method_exists($validator, 'setTranslator')) {
                if (method_exists($validator, 'hasTranslator')) {
                    if (!$validator->hasTranslator()) {
                        $validator->setTranslator($translator);
                    }
                } else {
                    $validator->setTranslator($translator);
                }
            }

            if (method_exists($validator, 'setDisableTranslator')) {
                $validator->setDisableTranslator($this->translatorIsDisabled());
            }

            if ($isArray && is_array($value)) {
                $messages = [];
                $errors   = [];
                if (empty($value)) {
                    if ($this->isRequired()
                        || (!$this->isRequired() && !$this->getAllowEmpty())
                    ) {
                        $value = '';
                    }
                }
                foreach ((array)$value as $val) {
                    if (!$validator->isValid($val, $context)) {
                        $result = false;
                        if ($this->_hasErrorMessages()) {
                            $messages = $this->_getErrorMessages();
                            $errors   = $messages;
                        } else {
                            $messages = array_merge($messages, $validator->getMessages());
                            $errors   = array_merge($errors, array_keys($validator->getMessages()));
                        }
                    }
                }
                if ($result) {
                    continue;
                }
            } elseif ($validator->isValid($value, $context)) {
                continue;
            } else {
                $result = false;
                if ($this->_hasErrorMessages()) {
                    $messages = $this->_getErrorMessages();
                    $errors   = $messages;
                } else {
                    $messages = $validator->getMessages();
                    $errors   = array_keys($messages);
                }
            }

            $result          = false;
            $this->_messages = array_merge($this->_messages, $messages);
            $this->_errors   = array_merge($this->_errors,   $errors);
        }

        // If element manually flagged as invalid, return false
        if ($this->_isErrorForced) {
            return false;
        }

        return $result;
    }

    /**
     * Get status of auto-register inArray validator flag
     *
     * @return bool
     */
    public function registerInArrayValidator()
    {
        if (! (isset($this->_registerInArrayValidator) && $this->_registerInArrayValidator)) {
            return false;
        }
        
        $multiOptions = $this->getMultiOptions();
        $options      = [];

        foreach ($multiOptions as $opt_value => $opt_label) {
            // optgroup instead of option label
            if (is_array($opt_label)) {
                $options = array_merge($options, array_keys($opt_label));
            }
            else {
                $options[] = $opt_value;
            }
        }

        $this->addValidator(
            'InArray',
            true,
            ['haystack' => $options]
        );
            
        return true;
    }
}
