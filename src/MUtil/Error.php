<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Error
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil;

/**
 * Generic functions for error handling
 *
 * @package    MUtil
 * @subpackage Error
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class Error
{
    /**
     * Get the string message
     *
     * @param string $defaultMessage Optional default message
     * @return string
     */
    public static function getLastPhpErrorMessage($defaultMessage = null)
    {
        $err = error_get_last();

        if (isset($err['message'])) {
            $needle = '>]:';
            $p      = strpos($err['message'], $needle);
            if (false === $p) {
                $err = $err['message'];
            } else {
                $err = trim(substr($err['message'], $p + strlen($needle)));
            }

            if ('No error' !== $err) {
                return $err;
            }
        }

        return $defaultMessage;
    }
}
