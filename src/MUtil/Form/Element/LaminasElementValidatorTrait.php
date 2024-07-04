<?php

namespace MUtil\Form\Element;

use Laminas\Filter\FilterInterface;
use Laminas\Validator\ValidatorInterface;

trait LaminasElementValidatorTrait
{
    /**
     * Element filters
     * @var array
     */
    protected $_filters = [];

    /**
     * Lazy-load a filter
     *
     * @param  array $filter
     * @return FilterInterface
     */
    protected function _loadFilter(array $filter)
    {
        $origName = $filter['filter'];
        $name     = $this->getPluginLoader(self::FILTER)->load($filter['filter']);

        if (array_key_exists($name, $this->_filters)) {
            require_once 'Zend/Form/Exception.php';
            throw new \Zend_Form_Exception(sprintf('Filter instance already exists for filter "%s"', $origName));
        }

        if (empty($filter['options'])) {
            $instance = new $name;
        } else {
            $r = new \ReflectionClass($name);
            if ($r->hasMethod('__construct')) {
                $instance = $r->newInstanceArgs(array_values((array) $filter['options']));
            } else {
                $instance = $r->newInstance();
            }
        }

        if ($origName != $name) {
            $filterNames  = array_keys($this->_filters);
            $order        = array_flip($filterNames);
            $order[$name] = $order[$origName];
            $filtersExchange = [];
            unset($order[$origName]);
            asort($order);
            foreach ($order as $key => $index) {
                if ($key == $name) {
                    $filtersExchange[$key] = $instance;
                    continue;
                }
                $filtersExchange[$key] = $this->_filters[$key];
            }
            $this->_filters = $filtersExchange;
        } else {
            $this->_filters[$name] = $instance;
        }

        return $instance;
    }

    /**
     * Lazy-load a validator
     *
     * @param  array $validator Validator definition
     * @return Zend_Validate_Interface
     */
    protected function _loadValidator(array $validator)
    {
        $origName = $validator['validator'];
        $name     = $this->getPluginLoader(self::VALIDATE)->load($validator['validator']);

        if (array_key_exists($name, $this->_validators)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception(sprintf('Validator instance already exists for validator "%s"', $origName));
        }

        $messages = false;
        if (isset($validator['options']) && array_key_exists('messages', (array)$validator['options'])) {
            $messages = $validator['options']['messages'];
            unset($validator['options']['messages']);
        }

        if (empty($validator['options'])) {
            $instance = new $name;
        } else {
            $r = new ReflectionClass($name);
            if ($r->hasMethod('__construct')) {
                $numeric = false;
                if (is_array($validator['options'])) {
                    $keys    = array_keys($validator['options']);
                    foreach($keys as $key) {
                        if (is_numeric($key)) {
                            $numeric = true;
                            break;
                        }
                    }
                }

                if ($numeric) {
                    $instance = $r->newInstanceArgs((array) $validator['options']);
                } else {
                    $instance = $r->newInstance($validator['options']);
                }
            } else {
                $instance = $r->newInstance();
            }
        }

        if ($messages) {
            if (is_array($messages)) {
                $instance->setMessages($messages);
            } elseif (is_string($messages)) {
                $instance->setMessage($messages);
            }
        }
        if (property_exists($instance, 'zfBreakChainOnFailure')) {
            $instance->zfBreakChainOnFailure = $validator['breakChainOnFailure'];
        }

        if ($origName != $name) {
            $validatorNames     = array_keys($this->_validators);
            $order              = array_flip($validatorNames);
            $order[$name]       = $order[$origName];
            $validatorsExchange = [];
            unset($order[$origName]);
            asort($order);
            foreach ($order as $key => $index) {
                if ($key == $name) {
                    $validatorsExchange[$key] = $instance;
                    continue;
                }
                $validatorsExchange[$key] = $this->_validators[$key];
            }
            $this->_validators = $validatorsExchange;
        } else {
            $this->_validators[$name] = $instance;
        }

        return $instance;
    }

    /**
     * Add a filter to the element
     *
     * @param  string|FilterInterface $filter
     * @return \Zend_Form_Element
     */
    public function addFilter($filter, $options = [])
    {
        if ($filter instanceof FilterInterface) {
            $name = get_class($filter);
        } elseif (is_string($filter)) {
            $name = $filter;
            $filter = [
                'filter' => $filter,
                'options' => $options,
            ];
            $this->_filters[$name] = $filter;
        } else {
            require_once 'Zend/Form/Exception.php';
            throw new \Zend_Form_Exception('Invalid filter provided to addFilter; must be string or Zend_Filter_Interface');
        }

        $this->_filters[$name] = $filter;

        return $this;
    }

    /**
     * Add filters to element
     *
     * @param  array $filters
     * @return \Zend_Form_Element
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $filterInfo) {
            if ($filterInfo instanceof FilterInterface) {
                $this->addFilter($filterInfo);
            } elseif (is_string($filterInfo)) {
                $this->addFilter($filterInfo);
            } elseif (is_array($filterInfo)) {
                $argc                = count($filterInfo);
                $options             = [];
                if (isset($filterInfo['filter'])) {
                    $filter = $filterInfo['filter'];
                    if (isset($filterInfo['options'])) {
                        $options = $filterInfo['options'];
                    }
                    $this->addFilter($filter, $options);
                } else {
                    switch (true) {
                        case (0 == $argc):
                            break;
                        case (1 <= $argc):
                            $filter  = array_shift($filterInfo);
                        case (2 <= $argc):
                            $options = array_shift($filterInfo);
                        default:
                            $this->addFilter($filter, $options);
                            break;
                    }
                }
            } else {
                dump(get_class($filterInfo));
                // require_once 'Zend/Form/Exception.php';
                // throw new \Zend_Form_Exception('Invalid filter passed to addFilters()');
            }
        }

        return $this;
    }

    /**
     * Add filters to element, overwriting any already existing
     *
     * @param  array $filters
     * @return \Zend_Form_Element
     */
    public function setFilters(array $filters)
    {
        $this->clearFilters();
        return $this->addFilters($filters);
    }

    /**
     * Retrieve a single filter by name
     *
     * @param  string $name
     * @return FilterInterface|bool
     */
    public function getFilter($name)
    {
        if (!isset($this->_filters[$name])) {
            $len = strlen($name);
            foreach ($this->_filters as $localName => $filter) {
                if ($len > strlen($localName)) {
                    continue;
                }

                if (0 === substr_compare($localName, $name, -$len, $len, true)) {
                    if (is_array($filter)) {
                        return $this->_loadFilter($filter);
                    }
                    return $filter;
                }
            }
            return false;
        }

        if (is_array($this->_filters[$name])) {
            return $this->_loadFilter($this->_filters[$name]);
        }

        return $this->_filters[$name];
    }

    /**
     * Get all filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = [];
        foreach ($this->_filters as $key => $value) {
            if ($value instanceof FilterInterface) {
                $filters[$key] = $value;
                continue;
            }
            $filter = $this->_loadFilter($value);
            $filters[get_class($filter)] = $filter;
        }
        return $filters;
    }

    /**
     * Remove a filter by name
     *
     * @param  string $name
     * @return \Zend_Form_Element
     */
    public function removeFilter($name)
    {
        if (isset($this->_filters[$name])) {
            unset($this->_filters[$name]);
        } else {
            $len = strlen($name);
            foreach (array_keys($this->_filters) as $filter) {
                if ($len > strlen($filter)) {
                    continue;
                }
                if (0 === substr_compare($filter, $name, -$len, $len, true)) {
                    unset($this->_filters[$filter]);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Clear all filters
     *
     * @return \Zend_Form_Element
     */
    public function clearFilters()
    {
        $this->_filters = [];
        return $this;
    }

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
