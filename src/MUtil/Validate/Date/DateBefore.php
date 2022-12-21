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

use DateTimeImmutable;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class DateBefore extends DateAbstract
{
    /**
     * Error constants
     */
    const NOT_BEFORE = 'notBefore';
    const NO_VALIDFROM = 'noValidFrom';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(
        self::NOT_BEFORE => "Date should be '%dateBefore%' or earlier.",
        self::NO_VALIDFROM => "Should be empty if valid after date is not set."
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'dateBefore' => '_beforeValue',
    );

    protected $_beforeDate;
    protected $_beforeValue;

    public function __construct($beforeDate = null, $format = 'dd-MM-yyyy')
    {
        parent::__construct($format);
        $this->_beforeDate = $beforeDate;
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
        $format = $this->getDateFormat();

        if (null === $this->_beforeDate) {
            $this->_beforeDate = new DateTimeImmutable();
        }

        $before = $this->getDateObject($this->_beforeValue);

        if ($before === null && is_array($this->_beforeDate) && array_key_exists($this->_beforeDate, $context)) {
            $before = $this->getDateObject($context[$this->_beforeDate]);
        }

        if (! $before) {
            $this->error(self::NO_VALIDFROM);
            return false;
        }
        $this->_beforeValue = $before->format($format);

        $check = $this->getDateObject($value);

        if ((! $check) || ($check->getTimestamp() > $before->getTimestamp())) {
            $this->error(self::NOT_BEFORE);
            return false;
        }

        return true;
    }
}
