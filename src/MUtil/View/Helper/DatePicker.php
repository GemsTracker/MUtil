<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage View\Helper
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\View\Helper;

/**
 *
 * @package    MUtil
 * @subpackage View\Helper
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class DatePicker extends \ZendX_JQuery_View_Helper_DatePicker
{
    /**
     * Create a jQuery UI Widget Date Picker
     *
     * @link   http://docs.jquery.com/UI/Datepicker
     * @link   http://trentrichardson.com/examples/timepicker
     *
     * @static boolean $sayThisOnlyOnce Output JavaScript only once.
     * @param  string $id
     * @param  string $value
     * @param  array  $params jQuery Widget Parameters
     * @param  array  $attribs HTML Element Attributes
     * @return string
     */
    public function datePicker($id, $value = null, array $params = array(), array $attribs = array())
    {
        static $sayThisOnlyOnce = true;

        if (isset($attribs['class'])) {
            $attribs['class'] .= ' form-control';
        } else {
            $attribs['class'] = ' form-control';
        }

        $attribs = $this->_prepareAttributes($id, $value, $attribs);
        $picker  = 'datepicker';

        $formatDate = isset($params['dateFormat']) && $params['dateFormat'];
        $formatTime = isset($params['timeFormat']) && $params['timeFormat'];

        // \MUtil\EchoOut\EchoOut::track($params['dateFormat'], $params['timeFormat']);
        if ((!isset($params['dateFormat'])) && (!isset($params['timeFormat'])) && \Zend_Registry::isRegistered('Zend_Locale')) {
            $params['dateFormat'] = self::resolveZendLocaleToDatePickerFormat();
        }
        if ($formatDate) {
            if ($formatTime) {
                $picker  = 'datetimepicker';
            }
        } elseif ($formatTime) {
            $picker  = 'timepicker';
        }
        if (isset($params['timeJsUrl'])) {
            $baseurl = $params['timeJsUrl'];
            unset($params['timeJsUrl']);
        } else {
            $baseurl = false;
        }

        $js = sprintf('%s("#%s").%s(%s);',
                \ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
                $attribs['id'],
                $picker,
                \ZendX_JQuery::encodeJson($params)
        );

        if ($formatTime && $sayThisOnlyOnce) {

            $files[] = 'jquery-ui-timepicker-addon.js';
            /*
            if ($locale = \Zend_Registry::get('Zend_Locale')) {
                $language = $locale->getLanguage();
                // We have a language, but only when not english
                if ($language && $language != 'en') {
                    $files[] = sprintf('i18n/jquery-ui-timepicker-addon-%s.js', $language);
                }
            } // */

            if ($baseurl) {
                foreach ($files as $file) {
                    $this->jquery->addJavascriptFile($baseurl . '/' . $file);
                }
            } else {
                foreach ($files as $file) {
                    if (file_exists(__DIR__ . '/js/' . $file)) {
                        $js = "\n// File: $file\n\n" . file_get_contents(__DIR__ . '/js/' . $file) . "\n\n" . $js;
                    }
                }
            }

            $sayThisOnlyOnce = false;
        }

        $this->jquery->addOnLoad($js);

        $onload = $this->onLoadJs(
                $id,
                $picker,
                (isset($attribs['readonly']) && $attribs['readonly']) ||
                    (isset($attribs['disabled']) && $attribs['disabled'])
                );

        $onload->render($this->view);

        $datePicker = '<div class="input-group date">'
            . $this->view->formText($id, $value, $attribs)
            . '<label for="' . $attribs['id'] . '" class="input-group-addon date"><i class="fa fa-calendar"></i></label>'
            . '</div>';

        return $datePicker;
    }

    /**
     * Create a JavaScript onload element
     *
     * @param string $id
     * @param string $picker
     * @param boolean $disabled
     * @return \MUtil\Html\Code\JavaScript
     */
    public function onLoadJs($id, $picker, $disabled)
    {
        $onload = new \MUtil\Html\Code\JavaScript(array('ELEM_ID' => $id, 'PICKER' => $picker));

        if ($disabled) {
            $onload->addContent(__DIR__ . '/js/datepicker.disabled.js');
        }

        return $onload;
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
     * Some examples:
     *  - "yyyy-MM-dd HH:mm"  => array("yy-mm-dd", " ", "hh:mm")
     *  - "X yyyy-MM-dd X"    => array("X yy-mm-dd X", false, false)
     *  - "yy \"hi': mm\" MM" => array("y 'hi'': mm' mm", false, false)
     *  - "yyyy-MM-dd 'date: yyyy-MM-dd' HH:mm 'time'': hh:mm' HH:mm Q", => array("yy-mm-dd", " 'date: yyyy-MM-dd' ", "HH:mm 'time'': HH:mm' z Q")
     *  - "HH:mm:ss"          => array(false, false, "HH:mm:ss")
     *  - \Zend_Date::ISO_8601 => array("ISO_8601", "T", "HH:mm:ssZ")
     *
     * @deprecated since version 1.4
     * @param string $format Or \Zend_Locale_Format::getDateFormat($locale)
     * @return array dateFormat, seperator, timeFormat
     */
    public static function splitZendLocaleToDateTimePickerFormat($format=null)
    {
        return \MUtil\Date\Format::splitDateTimeFormat($format);
    }
}