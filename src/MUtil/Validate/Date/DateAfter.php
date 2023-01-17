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
class DateAfter extends DateAbstract
{
    /**
     * Error constants
     */
    const NOT_AFTER = 'notAfter';
    const NO_VALIDFROM = 'noValidFrom';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = array(
        self::NOT_AFTER => "Date should be '%dateAfter%' or later.",
        self::NO_VALIDFROM => "Should be empty if valid from date is not set."
    );

    /**
     * @var array
     */
    protected $messageVariables = array(
        'dateAfter' => 'afterValue',
    );

    protected $afterDate;
    protected $afterValue;

    public function __construct($afterDate = null, $format = 'd-m-Y')
    {
        parent::__construct($format);
        $this->afterDate = $afterDate;
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
     */
    public function isValid($value, $context = null)
    {
        $format = $this->getDateFormat();

        if (null === $this->afterDate) {
            $after = new DateTimeImmutable();
        } elseif ($this->afterDate instanceof \DateTimeInterface) {
            $after = $this->beforeDate;
        } elseif (isset($context[$this->afterDate])) {
            $after = $this->getDateObject($context[$this->afterDate]);
        } else {
            $after = false;
        }

        if (! $after) {
            $this->error(self::NO_VALIDFROM);
            return false;
        }

        $this->afterValue = $after->format($this->getDateFormat());
        $check = $this->getDateObject($value);

        if ((! $check) || ($check->getTimestamp() < $after->getTimestamp())) {
            $this->error(self::NOT_AFTER);
            return false;
        }

        return true;
    }
}
