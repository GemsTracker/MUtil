<?php

namespace MUtil\Form\Element;

use Laminas\Validator\ValidatorInterface;

trait LaminasElementValidator
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
