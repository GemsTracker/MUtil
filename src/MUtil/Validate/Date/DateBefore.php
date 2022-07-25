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
class DateBefore extends \MUtil\Validate\Date\DateAbstract
{
    /**
     * Error constants
     */
    const NOT_BEFORE = 'notBefore';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(
        self::NOT_BEFORE => "Date should be '%dateBefore%' or earlier.",
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
        if (null === $this->_beforeDate) {
            $this->_beforeDate = new \Zend_Date();
        }

        if ($this->_beforeDate instanceof \Zend_Date) {
            $before = $this->_beforeDate;
        } elseif (isset($context[$this->_beforeDate])) {
            $before = new \Zend_Date($context[$this->_beforeDate], $this->getDateFormat());
        } else {
            $before = new \Zend_Date($this->_beforeDate);
        }
        $this->_beforeValue = $before->toString($this->getDateFormat());

        $check = new \Zend_Date($value, $this->getDateFormat());

        if ($check->isLater($before)) {
            $this->_error(self::NOT_BEFORE);
            return false;
        }

        return true;
    }
}
