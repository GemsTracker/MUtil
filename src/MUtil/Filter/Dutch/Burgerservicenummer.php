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

namespace MUtil\Filter\Dutch;

/**
 * Check the value and make sure there are enough zero's
 *
 * @package    MUtil
 * @subpackage Filter
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class Burgerservicenummer extends \Zend_Filter_Digits
{
    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws \Zend_Filter_Exception If filtering $value is impossible
     * @return mixed
     */
    public function filter($value)
    {
        $newValue = parent::filter($value);

        if (intval($newValue)) {
            return str_pad($newValue, 9, '0', STR_PAD_LEFT);
        }

        // Return as is when e.g. ********* or nothing
        return $value;
    }
}
