<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Translate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Translate\Adapter;

/**
 * A dummy translator that does nothing but make sure the is a translator adapter object
 *
 * @package    MUtil
 * @subpackage Translate
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Potemkin extends \Zend_Translate_Adapter
{
    protected function _loadTranslationData($data, $locale, array $options = array())
    {
        return array();
    }

    public static function create()
    {
        return new \Zend_Translate(__CLASS__, '');
    }

    public function setLocale($locale)
    {
        return $this;
    }

    public function toString()
    {
        return __CLASS__;
    }
}