<?PHP

/**
 *
 * @package    MUtil
 * @subpackage Javascript
 * @author     Jasper van Gestel <jvangestel@gmail.com>
 * @copyright  Copyright (c) 2019, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

namespace MUtil;

/**
 *
 * @package    MUtil
 * @subpackage Javascript
 * @copyright  Copyright (c) 2019, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 * @since      Class available since version 1.9.1
 */
class Javascript
{
    /**
     * @var string The nonce or empty if not used
     */
    public static $scriptNonce;

    /**
     * Set a valid nounce
     * 
     * @throws \Zend_Crypt_Exception
     */
    public static function generateNonce()
    {
        self::$scriptNonce = hash('sha256',
            \Zend_Crypt_Math::randBytes(512)
        );
    }

    /**
     * @return string The nounce as attribute string
     */
    public static function getNonceAttributeString()
    {
        if (self::$scriptNonce) {
            return ' nonce="' . self::$scriptNonce . '" '; 
        } 
        return '';
    }
}