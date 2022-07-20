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
            throw new \Zend_Form_Exception('Invalid validator provided to addValidator; must be string or Zend_Validate_Interface');
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
                throw new \Zend_Form_Exception('Invalid validator passed to addValidators()');
            }
        }

        return $this;
    }
}
