<?php

/**
 *
 * @package MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 201e Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Validate\Date;

/**
 *
 *
 * @package MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class IsDate extends \MUtil\Validate\Date\DateAbstract
{
    /**
     * Error constants
     */
    const NOT_VALID_DATE = 'notValidDate';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(
        self::NOT_VALID_DATE => '%value% is not a valid date.',
    );

    public $zfBreakChainOnFailure = true;

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return boolean
     * @throws \Zend_Valid_Exception If validation of $value is impossible
     */
    public function isValid($value, $context = null)
    {
        try {
            $date = new \Zend_Date($value, $this->getDateFormat());
        } catch (\Zend_Date_Exception $e) {
            $this->_error(self::NOT_VALID_DATE, $value);
            return false;
        }
        
        $year = $date->get(\Zend_Date::YEAR);

        /**
         * Prevent extreme dates (also fixes errors when saving to the db)
         */
        if ($year > 1850 && $year < 2200 && \Zend_Date::isDate($value, $this->getDateFormat())) {
            return true;
        }

        $this->_error(self::NOT_VALID_DATE, $value);
        return false;
    }
}
