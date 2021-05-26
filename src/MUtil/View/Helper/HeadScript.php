<?php

/**
 *
 * @package    MUtil
 * @subpackage View\Helper
 * @author     Jasper van Gestel <jvangestel@gmail.com>
 * @copyright  Copyright (c) 2021, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 */

use MUtil\Javascript;

/**
 *
 * @package    MUtil
 * @subpackage View\Helper
 * @copyright  Copyright (c) 2021, Erasmus MC and MagnaFacta B.V.
 * @license    No free license, do not copy
 * @since      Class available since version 1.9.1
 */
class MUtil_View_Helper_HeadScript extends \Zend_View_Helper_HeadScript
{
    /**
     * Optional allowed attributes for script tag
     * @var array
     */
    protected $_optionalAttributes = array(
        'charset', 'defer', 'language', 'nonce', 'src'
    );

    /**
     * Create data item containing all necessary components of script
     *
     * @param  string $type
     * @param  array $attributes
     * @param  string $content
     * @return stdClass
     */
    public function createData($type, array $attributes, $content = null)
    {
        $data = parent::createData($type, $attributes, $content);

        if (!array_key_exists('nonce', $attributes) && Javascript::$scriptNonce) {
            $data->nonce = Javascript::$scriptNonce;
            $data->attributes['nonce'] = Javascript::$scriptNonce;
        }

        return $data;
    }
}