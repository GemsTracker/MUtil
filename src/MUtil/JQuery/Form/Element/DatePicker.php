<?php

/**
 *
 * @package    MUtil
 * @subpackage JQuery
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\JQuery\Form\Element;

use DateTimeInterface;
use MUtil\Form\Element\NoTagsElementTrait;
use MUtil\Model;

/**
 * Extension of ZendX DatePicker element that add's locale awareness and input and output date
 * parsing to the original element.
 *
 * @see \ZendX_JQuery_Form_Element_DatePicker
 *
 * @package    MUtil
 * @subpackage JQuery
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class DatePicker extends \ZendX_JQuery_Form_Element_DatePicker
{
    use NoTagsElementTrait;

    /**
     *
     * @var string The date view format: how the user gets to see te date / datetime
     */
    protected $_dateFormat;

    /**
     *
     * @var DateTimeInterface The underlying value as a date object
     */
    protected $_dateValue;

    /**
     *
     * @var string The date storage format: how the storage engine delivers the date / datetime
     */
    protected $_storageFormat;

    /**
     * Set the underlying parent $this->_value as a string, reflecting the value
     * of $this->_dateValue.
     *
     * @return \MUtil\JQuery\Form\Element\DatePicker (continuation pattern)
     */
    protected function _applyDateFormat()
    {
        if ($this->_dateValue instanceof DateTimeInterface) {
            parent::setValue($this->_dateValue->format($this->getDateFormat()));
        }
        return $this;
    }

    /**
     * Get the date view format: how the user gets to see te date / datetime
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->_dateFormat;
    }

    /**
     * Return the value as a date object
     *
     * @return ?DateTimeInterface
     */
    public function getDateValue(): ?DateTimeInterface
    {
        if ($this->_value && (! $this->_dateValue)) {
            $this->setDateValue($this->_value);
        }
        return $this->_dateValue;
    }

    /**
     * Get the date storage format: how the storage engine delivers the date / datetime
     *
     * @return string
     */
    public function getStorageFormat()
    {
        return $this->_storageFormat;
    }

    /**
     * Validate element value
     *
     * If a translation adapter is registered, any error messages will be
     * translated according to the current locale, using the given error code;
     * if no matching translation is found, the original message will be
     * utilized.
     *
     * Note: The *filtered* value is validated.
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $validators = $this->getValidators();

        if (! $this->getValidator('IsDate')) {
            // Always as first validator
            $isDate = new \MUtil\Validate\Date\IsDate();
            $isDate->setDateFormat($this->_dateFormat);

            array_unshift($validators, $isDate);
            $this->setValidators($validators);
        }

        if ($format = $this->getDateFormat()) {
            // Set the dataFormat if settable
            foreach ($validators as $validator) {
                if (($validator instanceof \MUtil\Validate\Date\FormatInterface)
                    || method_exists($validator, 'setDateFormat')) {
                    $validator->setDateFormat($format);
                }
            }
        }

        return parent::isValid($value, $context);
    }

    /**
     * Set the date view format: how the user gets to see te date / datetime
     *
     * @param string $format
     * @return \MUtil\JQuery\Form\Element\DatePicker (continuation patern)
     */
    public function setDateFormat($format)
    {
        $view = $this->getView();

        list($dateFormat, $separator, $timeFormat) = self::splitTojQueryDateTimeFormat($format);

        if ($dateFormat) {
            $this->setJQueryParam('dateFormat', $dateFormat);
        }
        if ($separator) {
            $this->setJQueryParam('separator', $separator);
        }
        if ($timeFormat) {
            $this->setJQueryParam('timeFormat', $timeFormat);
        }

        $this->_dateFormat = $format;
        $this->_applyDateFormat();

        return $this;
    }

    /**
     * Set the both the _value (as a string) and the _dateValue (as an DateTimeInterface)
     *
     * @param string $format
     * @return \MUtil\JQuery\Form\Element\DatePicker (continuation patern)
     */
    public function setDateValue($value)
    {
        // \MUtil\EchoOut\EchoOut::r('Input: ' . $value);
        if (null === $value || '' === $value) {
            $this->_dateValue = null;
        } else {
            if ($value instanceof DateTimeInterface) {
                $this->_dateValue = $value;
            } else {
                $date = Model::getDateTimeInterface($value, [$this->getDateFormat(), $this->getStorageFormat()]);
                $this->_dateValue = $date ?: null;
            }
        }
        if ($this->_dateValue instanceof DateTimeInterface) {
            $this->_applyDateFormat();
        } else {
            parent::setValue($value);
        }
        return $this;
    }

    /**
     * Set the date storage format: how the storage engine delivers the date / datetime
     *
     * @param string $format
     * @return \MUtil\JQuery\Form\Element\DatePicker (continuation patern)
     */
    public function setStorageFormat($format)
    {
        $this->_storageFormat = $format;

        return $this;
    }

    /**
     * Set element value
     *
     * @param  mixed $value
     * @return \Zend_Form_Element
     */
    public function setValue($value)
    {
        $this->setDateValue($value);
        return $this;
    }

    /**
     * Set view object
     *
     * @param  \Zend_View_Interface $view
     * @return \Zend_Form_Element
     */
    public function setView(\Zend_View_Interface $view = null)
    {
        $element = parent::setView($view);

        if (null !== $view) {
            if (false === $view->getPluginLoader('helper')->getPaths('MUtil\JQuery_View_Helper')) {
                $view->addHelperPath('MUtil/JQuery/View/Helper', 'MUtil\JQuery_View_Helper');
            }
        }

        /*
         * These language files are no longer available in jQuery
        if ($locale = \Zend_Registry::get('Zend_Locale')) {
            $language = $locale->getLanguage();
            // We have a language, but only when not english
            if ($language && $language != 'en') {
                $jquery = $view->JQuery();

                if ($uiPath = $jquery->getUiLocalPath()) {
                    $baseUrl = dirname($uiPath);

                } else {
                    $baseUrl = \MUtil\Https::on() ? \ZendX_JQuery::CDN_BASE_GOOGLE_SSL : \ZendX_JQuery::CDN_BASE_GOOGLE;
                    $baseUrl .= \ZendX_JQuery::CDN_SUBFOLDER_JQUERYUI;
                    $baseUrl .= $jquery->getUiVersion();
                }
                // Option 1: download single language file
                if (version_compare($jquery->getUiVersion() , '1.11.0', '>=')) {
                    $jquery->addJavascriptFile($baseUrl . '/i18n/datepicker-' . $language . '.js');
                } else {
                    $jquery->addJavascriptFile($baseUrl . '/i18n/jquery.ui.datepicker-' . $language . '.js');
                }

                // Option 2: download all languages and select current
                // $jquery->addJavascriptFile($baseUrl . '/i18n/jquery-ui-i18n.min.js');
                // $jquery->addOnLoad("$.datepicker.setDefaults($.datepicker.regional['$language'])");

                // TODO: Option 3: enable language setting for each individual date
            }
        } // */

        return $element;
    }
    
    /**
     * This function splits a date time format into a date, separator and time part; the last two
     * only when there are time parts in the format.
     *
     * The results are formats readable by the jQuery Date/Time Picker.
     *
     * No date formats are allowed after the start of the time parts. (A future extension
     * might be to allow either option, but datetimepicker does not understand that.)
     *
     * @param string $format 
     * @return array dateFormat, seperator, timeFormat
     */
    public static function splitTojQueryDateTimeFormat($format)
    {
        // The output formats are jQuery DatePicker formats
        $fullDates = array(
            'c' => ['YYYY-MM-dd', 'T', 'HH:mm:ss'],
            'r' => ['ddd, d MMM YYYY', ' ', 'HH:mm:ss'],
            'd-m-Y' => ['dd-MM-YYYY', '', ''],
            'd-m-Y H:i' => ['dd-MM-YYYY', ' ', 'HH:mm'],
            'H:i' => ['', '', 'HH:mm'],
        );

        if (isset($fullDates[$format])) {
            return $fullDates[$format];
        }

        $dateFormats = array(
            'd' => 'dd', 'D' => 'ddd', 'j' => 'd', 'l' => 'dddd', 'N' => '', 'S' => '', 'w' => '', 'z' => '',
            'W' => '',
            'F' => 'MMMM', 'm' => 'MM', 'M' => 'MMM', 'n' => 'M', 't' => '',
            'L' => '', 'X' => 'YYYY', 'x' => 'YYYY', 'Y' => 'YYYY', 'y' => 'YY',
        );
        $timeFormats = array(
            'a' => 'tt', 'A' => 'tt', 'B' => '',
            'g' => 'h', 'G' => 'hh', 'h' => 'h', 'H' => 'hh',
            'i' => 'mm', 's' => 'ss', 'u' => '', 'v' => '',
            'e' => '', 'I' => '', 'P' => '', 'p' => '', 'T' => '', 'Z' => '',
        );

        $pregs[] = '"[^"]*"'; // Literal text
        $pregs[] = "'[^']*'"; // Literal text
        $pregs   = array_merge($pregs, array_keys($dateFormats), array_keys($timeFormats)); // Add key words
        $preg    = sprintf('/(%s)/', implode('|', $pregs));

        $parts = preg_split($preg, $format, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        $cache      = '';
        $dateFormat = false;
        $separator  = false;
        $timeFormat = false;

        foreach ($parts as $part) {
            if (isset($dateFormats[$part])) {
                if (false !== $timeFormat) {
                    throw new \Zend_Form_Element_Exception(sprintf(
                                                               'Date format specifier %s not allowed after time specifier in %s in format mask %s.',
                                                               $part,
                                                               $timeFormat,
                                                               $format
                                                           ));
                }
                $dateFormat .= $cache . $dateFormats[$part];
                $cache      = '';

            } elseif (isset($timeFormats[$part])) {
                // Switching to time format mode
                if (false === $timeFormat) {
                    if ($dateFormat) {
                        $separator  = $cache;
                        $timeFormat = $timeFormats[$part];
                    } else {
                        $timeFormat = $cache . $timeFormats[$part];
                    }
                } else {
                    $timeFormat .= $cache . $timeFormats[$part];
                }
                $cache = '';

            } elseif ('"' === $part[0]) {
                // Replace double quotes with single quotes, single quotes in string with two single quotes
                $cache .= strtr($part, array('"' => "'", "'" => "''"));

            } else {
                $cache .= $part;
            }
        }
        if ($cache) {
            if (false === $timeFormat) {
                $dateFormat .= $cache;
            } else {
                $timeFormat .= $cache;
            }
        }

        // \MUtil\EchoOut\EchoOut::track($preg);
        return array($dateFormat, $separator, $timeFormat);
    }
}
