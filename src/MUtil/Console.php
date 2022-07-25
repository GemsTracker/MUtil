<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Console
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil;

/**
 * Utilitu class for working with command line applications
 *
 * @package    MUtil
 * @subpackage Console
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.2
 */
class Console
{
    /**
     * True when php is running in command line mode
     *
     * @return boolean
     */
    public static function isConsole()
    {
        return !\Zend_Session::$_unitTestEnabled && (PHP_SAPI == 'cli');
    }

    /**
     * Mimics strip_tags but strips the content of certain tags (like script) too
     *
     * Function copied & adapted from a comment in http://www.php.net/manual/en/function.strip-tags.php#97386
     *
     * @param string $s The string to strip
     * @return string
     */
    public static function removeHtml($s)
    {
        $newLinesAfter = 'h1|h2|h3|h4|h5|h6|h7|h8';
        $newLineAfter  = 'caption|div|li|p|tr';
        $removeContent = 'script|style|noframes|select|option|link';
        $spaceAfter    = 'td|th';

        /**///prep the string
        $s = ' ' . preg_replace("/[\\r\\n]+/", '', $s);

        //begin removal
        /**///remove comment blocks
        while(stripos($s,'<!--') > 0){
            $pos[1] = stripos($s,'<!--');
            $pos[2] = stripos($s,'-->', $pos[1]);
            $len[1] = $pos[2] - $pos[1] + 3;
            $x = substr($s,$pos[1],$len[1]);
            $s = str_replace($x,'',$s);
        }

        /**///remove tags with content between them
        if(strlen($removeContent) > 0){
            $e = explode('|', $removeContent);
            for($i=0;$i<count($e);$i++){
                while(stripos($s,'<' . $e[$i]) > 0){
                    $len[1] = strlen('<' . $e[$i]);
                    $pos[1] = stripos($s,'<' . $e[$i]);
                    $pos[2] = stripos($s,$e[$i] . '>', $pos[1] + $len[1]);
                    $len[2] = $pos[2] - $pos[1] + $len[1];
                    $x = substr($s,$pos[1],$len[2]);
                    $s = str_replace($x,'',$s);
                }
            }
        }

        foreach (explode('|', $newLinesAfter) as $endTag) {
            $s = str_replace("</$endTag>", "\n\n", $s);
        }
        foreach (explode('|', $newLineAfter) as $endTag) {
            $s = str_replace("</$endTag>", "\n", $s);
        }
        foreach (explode('|', $spaceAfter) as $endTag) {
            $s = str_replace("</$endTag>", " ", $s);
        }

        /**///remove remaining tags
        $start = 0;
        while(stripos($s,'<', $start) > 0){
            $pos[1] = stripos($s,'<', $start);
            $pos[2] = stripos($s,'>', $pos[1]);
            if (!$pos[2]) {
                //No closing tag! Skip this one
                $start = $pos[1]+1;
            } else {
                $len[1] = $pos[2] - $pos[1] + 1;
                $x = substr($s,$pos[1],$len[1]);
                $s = str_replace($x,'',$s);
            }
        }

        if (PHP_EOL != "\n") {
            $s = str_replace("\n", PHP_EOL, $s);
        }
        return html_entity_decode(trim($s), ENT_QUOTES, 'cp1252');
    }
}
