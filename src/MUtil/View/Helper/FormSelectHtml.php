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
 *
 *
 * @package    MUtil
 * @subpackage View
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class FormSelectHtml extends \Zend_View_Helper_FormSelect
{
    /**
     * Variable containing the id of the select element
     *
     * @var string Contains the id of the select element
     */
    public $id;
    
    /**
     * Builds the actual <option> tag
     *
     * @param string $value Options Value
     * @param string $label Options Label
     * @param array  $selected The option value(s) to mark as 'selected'
     * @param array|bool $disable Whether the select is disabled, or individual options are
     * @param array $optionClasses The classes to associate with each option value
     * @return string Option Tag XHTML
     */
    protected function _build($value, $label, $selected, $disable, $optionClasses = array())
    {
        if (is_bool($disable)) {
            $disable = array();
        }
        
        $selectmenu = true;

        $opt = '<option'
             . ' value="' . $this->view->escape($value) . '"';

        if ($label instanceof \MUtil\Html\HtmlElement) {
            // Element not allowed, get parts that are allowed
            foreach (array('class', 'dir', 'id', 'label', 'lang', 'style', 'title', 'data-class', 'data-style') as $attr) {
                if (isset($label->$attr)) {
                    $opt .= ' ' . $attr . '="' . $this->view->escape($label->$attr) . '"';
                    if (('data-style' == $attr or 'data-class' == $attr) and true == $selectmenu) {
                        $this->enableSelectmenu();
                        $selectmenu = false;
                    }
                }
            }

            // Now get the content
            $renderer = \MUtil\Html::getRenderer();
            $content  = '';
            foreach ($label->getIterator() as $part) {
                $content .= $renderer->renderAny($this->view, $part);
            }
            
        } elseif ($label instanceof \MUtil\Html\HtmlInterface) {
            $content = $label->render($this->view);
        } else {
            $content = $this->view->escape($label);
            $opt .= ' label="' . $this->view->escape($label) . '"';

        }

        // selected?
        if (in_array((string) $value, $selected)) {
            $opt .= ' selected="selected"';
        }

        // disabled?
        if (in_array($value, $disable)) {
            $opt .= ' disabled="disabled"';
        }

        $opt .= '>' . $content . "</option>";
        
        return $opt;
    }
    /**
     * Generates 'select' list of options.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The option value to mark as 'selected'; if an
     * array, will mark all values in the array as 'selected' (used for
     * multiple-select elements).
     *
     * @param array|string $attribs Attributes added to the 'select' tag.
     *
     * @param array $options An array of key-value pairs where the array
     * key is the radio value, and the array value is the radio text.
     *
     * @param string $listsep When disabled, use this list separator string
     * between list values.
     *
     * @return string The select tag and options XHTML.
     */
    public function formSelectHtml($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        $this->id = $name;
        
        return parent::formSelect($name, $value, $attribs, $options, $listsep);
    }
    
    /**
     * Add necessary files and javascript for jQuery UI selectmenu
     */
    public function enableSelectmenu ()
    {
        $baseUrl = \Zend_Controller_Front::getInstance()->getBaseUrl();
        $this->view->headScript()->prependFile($baseUrl . '/gems/js/jquery-ui-selectmenu.js');
        $this->view->headLink()->appendStylesheet($baseUrl . '/gems/css/jquery-ui.css');
        $this->view->headLink()->appendStylesheet($baseUrl . '/gems/css/jquery-ui-selectmenu.css');
        
        $js = sprintf("jQuery(document).ready(function($) {
                $('#%s').iconselectmenu({width: null}).iconselectmenu('menuWidget').addClass('ui-menu-icons avatar overflow');
            });",
            $this->id
        );
        
        $this->view->headScript()->appendScript($js);
    }
}
