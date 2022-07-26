<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage View
 * @author     Jasper van Gestel<jappie@dse.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\View\Helper;

/**
 * Add bootstrap error classes
 *
 * @package    MUtil
 * @subpackage View
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */
class FormErrors extends \Zend_View_Helper_FormErrors
{
    public function formErrors($errors, array $options = null)
    {
        if (empty($options['class'])) {
            $options['class'] = 'errors alert alert-danger';
        }

        $html = parent::formErrors($errors, $options);

        return $html;
    }
}
