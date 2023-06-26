<?php

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Michiel Rook <michiel@touchdownconsulting.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Validator;

use Laminas\Validator\AbstractValidator;

/**
 * Validate that a Pdf can be loaded by \Zend_Pdf
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class Pdf extends AbstractValidator
{
    /**
     * Error constants
     */
    const ERROR_INVALID_VERSION = 'invalidVersion';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(
        self::ERROR_INVALID_VERSION => 'Unsupported PDF version: %value% Use PDF versions 1.0 - 1.4 to avoid this problem.'
    );

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return boolean
     * @throws \Zend_Pdf_Exception If validation of $value is impossible
     */
    public function isValid($value, $context = array())
    {
        // Only fail when file really can't be loaded.
        try {
            \Zend_Pdf::load($value);
            return true;
        } catch (\Zend_Pdf_Exception $e) {
            $this->_error(self::ERROR_INVALID_VERSION, $e->getMessage());
            return false;
        }
    }
}
