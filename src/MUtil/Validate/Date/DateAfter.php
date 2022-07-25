<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Validate\Date;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class DateAfter extends \MUtil\Validate\Date\DateAbstract
{
    /**
     * Error constants
     */
    const NOT_AFTER = 'notAfter';
    const NO_VALIDFROM = 'noValidFrom';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(
        self::NOT_AFTER => "Date should be '%dateAfter%' or later.",
        self::NO_VALIDFROM => "Should be empty if valid from date is not set."
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'dateAfter' => '_afterValue',
    );

    protected $_afterDate;
    protected $_afterValue;

    public function __construct($afterDate = null, $format = 'dd-MM-yyyy')
    {
        parent::__construct($format);
        $this->_afterDate = $afterDate;
    }

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
        if (null === $this->_afterDate) {
            $this->_afterDate = new \Zend_Date();
        }

        if ($this->_afterDate instanceof \Zend_Date) {
            $after = $this->_afterDate;
        } elseif (isset($context[$this->_afterDate])) {
            if (empty($context[$this->_afterDate])) {
                $this->_error(self::NO_VALIDFROM);
                return false;
            }

            $after = new \Zend_Date($context[$this->_afterDate], $this->getDateFormat());
        } elseif (\Zend_Date::isDate($this->_afterDate, $this->getDateFormat())) {
            $after = new \Zend_Date($this->_afterDate, $this->getDateFormat());
        } else {
            // No date specified, return true
            return true;
        }
        $this->_afterValue = $after->toString($this->getDateFormat());

        $check = new \Zend_Date($value, $this->getDateFormat());

        if ($check->isEarlier($after)) {
            $this->_error(self::NOT_AFTER);
            return false;
        }

        return true;
    }
}
