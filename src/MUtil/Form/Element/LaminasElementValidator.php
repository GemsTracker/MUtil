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
}
