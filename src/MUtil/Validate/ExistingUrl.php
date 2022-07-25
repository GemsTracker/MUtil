<?php

namespace MUtil\Validate;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Hostname;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class ExistingUrl extends AbstractValidator
{
    /**
     * Error constants
     */
    public const ERROR_SITE_NOT_FOUND = 'siteNotFound';
    public const ERROR_URL_NOT_VALID = 'urlNotFound';

    /**
     * @var bool Disable checking of url's for installations behind firewalls
     */
    public static bool $disabled = false;
    
    /**
     * @var array Message templates
     */
    protected array $messageTemplates = [
        self::ERROR_SITE_NOT_FOUND => 'The site %value%" does not exist',
        self::ERROR_URL_NOT_VALID  => '"%value%" is not a valid url',

        Hostname::INVALID                 => "Invalid type given, value should be a string",
        Hostname::IP_ADDRESS_NOT_ALLOWED  => "'%value%' appears to be an IP address, but IP addresses are not allowed",
        Hostname::UNKNOWN_TLD             => "'%value%' appears to be a DNS hostname but cannot match TLD against known list",
        Hostname::INVALID_DASH            => "'%value%' appears to be a DNS hostname but contains a dash (-) in an invalid position",
        Hostname::INVALID_HOSTNAME_SCHEMA => "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'",
        Hostname::UNDECIPHERABLE_TLD      => "'%value%' appears to be a DNS hostname but cannot extract TLD part",
        Hostname::INVALID_HOSTNAME        => "'%value%' does not match the expected structure for a DNS hostname",
        Hostname::INVALID_LOCAL_NAME      => "'%value%' does not appear to be a valid local network name",
        Hostname::LOCAL_NAME_NOT_ALLOWED  => "'%value%' appears to be a local network name but local network names are not allowed",
        Hostname::CANNOT_DECODE_PUNYCODE  => "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded"
    ];

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
    public function isValid($value): bool
    {
        $this->setValue($value);

        if ($value) {
            try {

                $url = filter_var($value, FILTER_VALIDATE_URL);
                if ($url === false) {
                    $this->error(self::ERROR_URL_NOT_VALID);
                    return false;
                }

                $urlParts = parse_url($url);

                if (!isset($urlParts['host'])) {
                    $this->error(self::ERROR_URL_NOT_VALID);
                    return false;
                }

                // Check the host against the allowed values; delegated to \Zend_Filter.
                $validate = new Hostname(Hostname::ALLOW_DNS | Hostname::ALLOW_IP | Hostname::ALLOW_LOCAL);

                if (! $validate->isValid($urlParts['host'])) {
                    foreach ($validate->getMessages() as $key => $msg) {
                        $this->error($key);
                    }
                    return false;
                }

                if (self::$disabled) {
                    // Do not perform check when disabled
                    return true;
                }

                if (function_exists('curl_init')) {
                    $ch = curl_init($value);

                    if (false === $ch) {
                        $this->error(self::ERROR_URL_NOT_VALID);
                        return false;
                    }

                    // Authentication
                    // if ($usr) {
                        // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                        // curl_setopt($ch, CURLOPT_USERPWD, $usr.':'.$pwd);
                    // }

                    // curl_setopt($ch, CURLOPT_FILETIME, true);
                    curl_setopt($ch, CURLOPT_NOBODY, true);

                    /**
                     * @todo Unknown CA's should probably be imported...
                     */
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                    $valid = curl_exec($ch);
                    if (! $valid) {
                        $this->error(self::ERROR_SITE_NOT_FOUND);
                    }

                    // $return = curl_getinfo($ch, CURLINFO_FILETIME);
                    // \MUtil\EchoOut\EchoOut::r('Date at server: '.date('r', $return));

                    curl_close($ch);

                    return $valid;

                } else {
                    return true;
                }

            } catch (\Exception $e) {
                $this->error(self::ERROR_URL_NOT_VALID);
                $this->setMessage($e->getMessage(), self::ERROR_URL_NOT_VALID);

                return false;
            }
        }
    }
}
