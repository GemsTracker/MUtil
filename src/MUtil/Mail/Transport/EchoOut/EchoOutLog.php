<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Mail
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Mail\Transport\EchoOut;

/**
 * Zend Mail transport adapter that does not send the mail, but just echo's the output.
 *
 * @package    MUtil
 * @subpackage Mail
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.4.4
 */
class EchoOutLog extends \Zend_Mail_Transport_Abstract
{
    /**
     * Send an email independent from the used transport
     *
     * The requisite information for the email will be found in the following
     * properties:
     *
     * - {@link $recipients} - list of recipients (string)
     * - {@link $header} - message header
     * - {@link $body} - message body
     */
    protected function _sendMail()
    {
        \MUtil\EchoOut\EchoOut::r(
                reset($this->_headers['Subject']),
                reset($this->_headers['From']) . '=>' . reset($this->_headers['To'])
                );
    }
}