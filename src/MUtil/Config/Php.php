<?php


/**
 *
 *
 * @package    MUtil
 * @subpackage Config
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2010 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Config;

/**
 * Get and parse the phpinf() data for display within the applications own layout
 *
 *
 * @package    MUtil
 * @subpackage Date
 * @copyright  Copyright (c) 2010 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class Php
{
    public $infoHtml;
    public $infoStyle;

    public function __construct($what = INFO_ALL, $cleanXHtml = true)
    {
        ob_start();
        phpinfo($what); // INFO_GENERAL & INFO_CONFIGURATION & INFO_MODULES & INFO_ENVIRONMENT & INFO_VARIABLES);
        $info = ob_get_clean();

        $this->infoStyle = trim(self::getTag($info, 'style'));
        $this->infoStyle = str_replace(array("body", "\n", ", "), array("div.php-info", "\ndiv.php-info ", ", div.php-info "), $this->infoStyle);
        $this->infoHtml  = '<div class="php-info">' . self::getTag($info, 'body') . '</div>';
        if ($cleanXHtml) {
            $this->infoHtml  = str_replace(
                array('<font ', '</font>', ' border="0" '),
                array('<span ', '</span>', ' style="border: 0px; " '),
                $this->infoHtml);
        }
    }

    public function getInfo()
    {
        return $this->infoHtml;
    }

    public function getStyle()
    {
        return $this->infoStyle;
    }

    public static function getTag($html, $tag, $includetag = false)
    {
        $p = strpos($html, '<'.$tag.'>');
        if (! $p) {
            $p = strpos($html, '<'.$tag.' ');
        }
        if ($includetag) {
            return substr($html, $p, strpos($html, '</'.$tag.'>', $p) - $p + strlen($tag) + 3);
        } else {
            $p = strpos($html, '>', $p) + 1;
            return substr($html, $p, strpos($html, '</'.$tag.'>', $p) - $p);
        }
    }
}