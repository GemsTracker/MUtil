<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2016 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Validator;

/**
 * Validates the a value is not the same as some other field value,
 * except when it is one of the exception values
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2016 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.7.2 Mar 22, 2016 3:16:44 PM
 */
class NotEqualExcept extends NotEqualTo
{
    /**
     * The exceptions where equality does not matter
     *
     * @var array
     */
    protected $exceptions;

    /**
     * Sets validator options
     *
     * @param array|string $fields On or more values that this element should not have
     * @param array|string $exceptions On or more values that this element can have
     * @param string|array Optional different message or an array of messages containing field names, an int array value is set as a general message
     */
    public function __construct($fields, $exceptions, $message = null)
    {
        parent::__construct($fields, $message);

        $this->exceptions = (array) $exceptions;
    }

    /**
     * Defined by ValidatorInterface
     *
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value, $context = array())
    {
        if ($value) {
            foreach ($this->exceptions as $exception) {
                if ($value == $exception) {
                    return true;
                }
                if (isset($context[$exception]) && ($value == $context[$exception])) {
                    return true;
                }
            }
        }

        return parent::isValid($value, $context);
    }
}
