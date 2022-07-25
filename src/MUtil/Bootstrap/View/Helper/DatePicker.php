<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Bootstrap
 * @author     Menno Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Bootstrap\View\Helper;

/**
 *
 * @package    MUtil
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.6.5
 */
class DatePicker extends \MUtil\JQuery\View\Helper\DatePicker
{
    public function datePicker($id, $value = null, array $params = array(), array $attribs = array())
    {
        if (isset($attribs['class'])) {
            $attribs['class'] .= ' form-control';
        } else {
            $attribs['class'] = ' form-control';
        }
        $datePicker = parent::datePicker($id, $value, $params, $attribs);

        $datePicker = '<div class="input-group date">'
                       . $datePicker
                       . '<label for="' . $attribs['id'] . '" class="input-group-addon date"><i class="fa fa-calendar"></i></label>'
                       . '</div>';

        return $datePicker;
    }
}
