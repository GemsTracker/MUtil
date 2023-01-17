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
    protected $messageTemplates = array(
        self::NOT_BEFORE => "Date should be '%dateBefore%' or earlier.",
        self::NO_VALIDFROM => "Should be empty if valid after date is not set."
    );

    /**
     * @var array
     */
    protected $messageVariables = array(
        'dateBefore' => 'beforeValue',
    );

    protected $beforeDate;
    protected $beforeValue;

    public function __construct($beforeDate = null, $format = 'd-m-Y')
    {
        parent::__construct($format);
        $this->beforeDate = $beforeDate;
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
        file_put_contents('data/logs/echo.txt', __CLASS__ . '->' . __FUNCTION__ . '(' . __LINE__ . '): ' .  get_class($value) . "\n", FILE_APPEND);
        $format = $this->getDateFormat();

        if (null === $this->beforeDate) {
            $before = new DateTimeImmutable();
        } elseif ($this->beforeDate instanceof \DateTimeInterface) {
            $before = $this->beforeDate;
        } elseif (isset($context[$this->beforeDate])) {
            $before = $this->getDateObject($context[$this->beforeDate]);
        } else {
            $before = false;
        }
        
        if (! $before) {
            $this->error(self::NO_VALIDFROM);
            return false;
        }
       
        $this->beforeValue = $before->format($this->getDateFormat());
        $check = $this->getDateObject($value);

        if ((! $check) || ($check->getTimestamp() > $before->getTimestamp())) {
            $this->error(self::NOT_BEFORE);
            return false;
        }

        return true;
    }
}
