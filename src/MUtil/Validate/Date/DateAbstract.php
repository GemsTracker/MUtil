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

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
abstract class DateAbstract extends \Zend_Validate_Abstract
        implements \MUtil\Validate\Date\FormatInterface
{
    // Always use accossor functions, never reference these vars straight
    private $_dateFormat;
    private $_locale;

    public function __construct($dateFormat = null)
    {
        if (null !== $dateFormat) {
            $this->setDateFormat($dateFormat);
        }
    }

    public function getDateFormat()
    {
        if (! $this->_dateFormat) {
            if ($locale = $this->getLocale()) {
                $this->setDateFormat(\Zend_Locale_Format::getDateFormat($locale));
            } else {
                $this->setDateFormat(\Zend_Date::DATE_SHORT);
            }
        }

        return $this->_dateFormat;
    }

    public function getLocale()
    {
        if ((! $this->_locale) && \Zend_Registry::isRegistered('Zend_Locale')) {
            $this->setLocale(\Zend_Registry::get('Zend_Locale'));
        }

        return $this->_locale;
    }

    public function setDateFormat($dateFormat)
    {
        $this->_dateFormat = $dateFormat;
        return $this;
    }

    public function setLocale(\Zend_Locale $locale)
    {
        $this->_locale = $locale;
        return $this;
    }
}
