<?php

/**
 *
 * @package    MUtil
 * @subpackage View
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

/**
 *
 * @package    MUtil
 * @subpackage View
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_View_Helper_Html extends \Zend_View_Helper_FormElement
{
    /**
     * Generates a fake element that just displays the item with a hidden extra value field.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The element value.
     * @param array $attribs Attributes for the element tag.
     * @return string The element XHTML.
     */
    public function html($name, $value = null, $attribs = null)
    {
        return \MUtil_Html::renderAny($this->view, $value);
    }
}
