<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Date
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Date;

/**
 * A static helper class to do stuff with date/time formats
 *
 * @package    MUtil
 * @subpackage Date
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.4 27-feb-2014 13:16:26
 */
class Format
{
    /**
     * Return the time part of a format
     *
     * @param string $format
     * @return string date, dateTime, time
     */
    public static function getDateTimeType($format = null)
    {
        if (null === $format) {
            return 'date';
        }

        $fullFormats = array(
            \Zend_Date::DATES             => 'date',
            \Zend_Date::DATE_FULL         => 'date',
            \Zend_Date::DATE_LONG         => 'date',
            \Zend_Date::DATE_MEDIUM       => 'date',
            \Zend_Date::DATE_SHORT        => 'date',
            \Zend_Date::TIMES             => 'time',
            \Zend_Date::TIME_FULL         => 'time',
            \Zend_Date::TIME_LONG         => 'time',
            \Zend_Date::TIME_MEDIUM       => 'time',
            \Zend_Date::TIME_SHORT        => 'time',
            \Zend_Date::DATETIME          => 'datetime',
            \Zend_Date::DATETIME_FULL     => 'datetime',
            \Zend_Date::DATETIME_LONG     => 'datetime',
            \Zend_Date::DATETIME_MEDIUM   => 'datetime',
            \Zend_Date::DATETIME_SHORT    => 'datetime',
            \Zend_Date::ATOM              => 'datetime',
            \Zend_Date::COOKIE            => 'datetime',
            \Zend_Date::ISO_8601          => 'datetime',
            \Zend_Date::RFC_822           => 'datetime',
            \Zend_Date::RFC_850           => 'datetime',
            \Zend_Date::RFC_1036          => 'datetime',
            \Zend_Date::RFC_1123          => 'datetime',
            \Zend_Date::RFC_2822          => 'datetime',
            \Zend_Date::RFC_3339          => 'datetime',
            \Zend_Date::RSS               => 'datetime',
            \Zend_Date::W3C               => 'datetime',
            );
        if (isset($fullFormats[$format])) {
            return $fullFormats[$format];
        }

        list($dateFormat, $separator, $timeFormat) = self::splitDateTimeFormat($format);

        if ($timeFormat) {
            if ($dateFormat) {
                return 'datetime';
            } else {
                return 'time';
            }
        } else {
            return 'date';
        }
    }

    /**
     * Return the time part of a format
     *
     * @param string $format
     * @return string
     */
    public static function getTimeFormat($format = null)
    {
        list($dateFormat, $separator, $timeFormat) = self::splitDateTimeFormat($format);

        return $timeFormat;
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
     * @param string $format Or \Zend_Locale_Format::getDateFormat($locale)
     * @return array dateFormat, seperator, timeFormat
     */
    public static function splitDateTimeFormat($format=null)
    {
        if($format == null) {
            $locale = \Zend_Registry::get('Zend_Locale');
            if(! ($locale instanceof \Zend_Locale) ) {
                throw new \ZendX_JQuery_Exception("Cannot resolve Zend Locale format by default, no application wide locale is set.");
            }
            /**
             * @see \Zend_Locale_Format
             */
            $format = \Zend_Locale_Format::getDateFormat($locale);
        }

        $fullDates = array(
            \Zend_Date::ATOM     => array('ATOM',     'T', 'HH:mm:ssZ' ), // No timezone +01:00, use +0100
            \Zend_Date::COOKIE   => array('COOKIE',   ' ', 'HH:mm:ss z'),
            \Zend_Date::ISO_8601 => array('ISO_8601', 'T', 'HH:mm:ssZ' ),
            \Zend_Date::RFC_822  => array('RFC_822',  ' ', 'HH:mm:ss Z'), // No timezone +01:00, use +0100
            \Zend_Date::RFC_850  => array('RFC_850',  ' ', 'HH:mm:ss z'),
            \Zend_Date::RFC_1036 => array('RFC_1036', ' ', 'HH:mm:ss Z'),
            \Zend_Date::RFC_1123 => array('RFC_1123', ' ', 'HH:mm:ss z'),
            \Zend_Date::RFC_2822 => array('RFC_2822', ' ', 'HH:mm:ss Z'),
            \Zend_Date::RFC_3339 => array('yy-mm-dd', 'T', 'HH:mm:ssZ' ), // No timezone +01:00, use +0100
            \Zend_Date::RSS      => array('RSS',      ' ', 'HH:mm:ss Z'),
            \Zend_Date::W3C      => array('W3C',      'T', 'HH:mm:ssZ' ), // No timezone +01:00, use +0100
        );

        if (isset($fullDates[$format])) {
            return $fullDates[$format];
        }

        $dateFormats = array(
            'EEEEE' => 'D', 'EEEE' => 'DD', 'EEE' => 'D', 'EE' => 'D', 'E' => 'D',
            'YYYYY' => 'yy', 'YYYY' => 'yy', 'YYY' => 'yy', 'YY' => 'y', 'Y' => 'yy',
            'yyyyy' => 'yy', 'yyyy' => 'yy', 'yyy' => 'yy', 'yy' => 'y', 'y' => 'yy',
            'MMMM' => 'MM', 'MMM' => 'M', 'MM' => 'mm', 'M' => 'm',
            'dd' => 'dd', 'd' => 'd', 'DDD' => 'oo', 'DD' => 'o', 'D' => 'o',
            'G' => '', 'e' => '', 'w' => '',
        );
        $timeFormats = array(
            'a' => 'tt', 'hh' => 'hh', 'h' => 'h', 'HH' => 'HH',
            'H' => 'H', 'mm' => 'mm', 'm' => 'm', 'ss' => 'ss', 's' => 's', 'S' => 'l',
            'zzzz' => 'z', 'zzz' => 'z', 'zz' => 'z', 'z' => 'z', 'ZZZZ' => 'Z',
            'ZZZ' => 'Z', 'ZZ' => 'Z', 'Z' => 'Z', 'A' => '',
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
