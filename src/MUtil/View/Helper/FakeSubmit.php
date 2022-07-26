<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage View
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\View\Helper;

/**
 * Helper to generate a "fake submit button" element
 *
 * @package    MUtil
 * @subpackage View
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class FakeSubmit extends \Zend_View_Helper_FormButton
{
    private function _extractValue($name, array &$attribs)
    {
        if (isset($attribs[$name])) {
            $value = $attribs[$name];
        } else {
            $value = null;
        }

        unset($attribs[$name]);
        return $value;
    }

    /**
     * Generates a 'fake submit button' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function fakeSubmit($name, $value = null, $attribs = null)
    {
        $target_name = $this->_extractValue('target', $attribs);
        $target_value = $this->_extractValue('targetValue', $attribs);
        $is_element = $this->_extractValue('targetValueIsElement', $attribs);

        if (! $target_value) {
            // the default value is the Label content
            $target_value = $value ? $value : $attribs['content'];
        }

        // WHY EXPLANATION
        //
        // First I tried to change the .type of the button to 'submit' onclick.
        // That does not work in IE and Firefox.

        if (is_null($target_name)) {
            // If no target was specified, there does not exists a form element to receive
            // the value that states that this element was clicked.
            //
            // So we create a new hidden element with the current name and perform a minimal
            // change on the current name so that no '' empty string value is returned
            // when the button is not clicked.
            $target_name = $name;
            $name = '_' . $name;

            $prehtml = $this->_hidden($target_name);
        } else {
            $prehtml = '';
        }

        // Link to page.
        if ($is_element) {
            $attribs['onclick'] = 'e1 = document.getElementsByName(\''.$target_name.'\')[0]; e2 = document.getElementsByName(\''.$target_value.'\')[0]; e1.value=e2.value; e.form.submit();';
        } else {
            $attribs['onclick'] = 'e = document.getElementsByName(\''.$target_name.'\')[0]; e.value=\''.$target_value.'\'; e.form.submit();';
        }

         return $prehtml . $this->formButton($name, $value, $attribs);
    }
}
