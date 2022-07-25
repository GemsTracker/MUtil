<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Filter
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 201e Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Filter;

/**
 *
 *
 * @package    MUtil
 * @subpackage Filter
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class LooseArrayFilter implements \Zend_Filter_Interface
{
    /**
     *
     * @var uppercase translate value => actual value
     */
    private $_basicValues = array();

    /**
     *
     * @var uppercase array translate value => actual value
     */
    private $_extraValues = array();

    /**
     *
     * @param array $options key => label
     * @param array $extraValues extra key value => actual value
     */
    public function __construct(array $options, array $extraValues = null)
    {
        $this->setMultiOptions($options);

        if (is_array($extraValues)) {
            $this->setExtraTranslations($extraValues);
        }
    }

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws \Zend_Filter_Exception If filtering $value is impossible
     * @return mixed
     */
    public function filter($value)
    {
        $test = strtoupper($value);

        if (array_key_exists($test, $this->_basicValues)) {
            return $this->_basicValues[$test];
        }
        if (array_key_exists($test, $this->_extraValues)) {
            return $this->_extraValues[$test];
        }

        return $value;
    }

    /**
     * Set any extra tranlations, e.g. V => F or MO => Monday
     *
     * @param array $options extra  key value => actual value
     * @return \MUtil\Filter\LooseArrayFilter (Continuation pattern)
     */
    public function setExtraTranslations(array $options)
    {
        $this->_extraValues = array_combine(array_map('strtoupper', array_keys($options)), $options);

        return $this;
    }

    /**
     * The basic options of the element
     *
     * @param array $options key => label
     * @return \MUtil\Filter\LooseArrayFilter (Continuation pattern)
     */
    public function setMultiOptions(array $options)
    {
        $keys = array_keys($options);
        $this->_basicValues =
                array_combine(array_map('strtoupper', $keys), $keys) +
                array_combine(array_map('strtoupper', $options), $keys);

        return $this;
    }

}
