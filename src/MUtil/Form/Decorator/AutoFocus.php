<?php

/**
 *
 * @package    MUtil
 * @subpackage Form
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

use MUtil\Bootstrap\Form\Element\Hidden as BootstrapHidden;
use MUtil\Form\Element\Hidden as BaseHidden;

/**
 * Form decorator that sets the focus on the first suitable element.
 *
 * @package    MUtil
 * @subpackage Form
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Form_Decorator_AutoFocus extends \Zend_Form_Decorator_Abstract
{
    /**
     * @param mixed $element
     * @param array $allowedElements
     */
    private function _getAllowed($element, array &$allowedElements)
    {
        // \MUtil_Echo::r(get_class($element));
        if ($element instanceof \MUtil_Form_Element_SubFocusInterface) {
            foreach ($element->getSubFocusElements() as $subElement) {
                $this->_getAllowed($subElement, $allowedElements);
            }
        } elseif ($element instanceof \Zend_Form_Element) {
            if (($element instanceof \Zend_Form_Element_Hidden) ||
                ($element instanceof \MUtil_Form_Element_NoFocusInterface) ||
                ($element->getAttrib('readonly')) ||
                ($element->helper == 'Button') ||
                ($element->helper == 'formSubmit') ||
                ($element->helper == 'SubmitButton')) {
                // Do nothing
            } else {
                $id = $element->getId();
                if ($id === null) {
                    $id = $element->getName();
                }
                $allowedElements[] = $id;
                
                if ($element instanceof \Zend_Form_Element_MultiCheckbox) {
                    foreach ($element->getMultiOptions() as $key => $label) {
                        $allowedElements[] = $id . '-' . $key;
                    }
                }
            }

        } elseif (($element instanceof \Zend_Form) ||
            ($element instanceof \Zend_Form_DisplayGroup)) {
            foreach ($element as $subElement) {
                $this->_getAllowed($subElement, $allowedElements);
            }
        }
    }

    /**
     *
     * @param mixed $element
     * @return string Element ID
     */
    private function _getFocus($element)
    {
        // \MUtil_Echo::r(get_class($element));
        if ($element instanceof \MUtil_Form_Element_SubFocusInterface) {
            foreach ($element->getSubFocusElements() as $subelement) {
                if ($focus = $this->_getFocus($subelement)) {
                    return $focus;
                }
            }
        } elseif ($element instanceof \Zend_Form_Element) {
            if (($element instanceof \Zend_Form_Element_Hidden) ||
                ($element instanceof \MUtil_Form_Element_NoFocusInterface) ||
                ($element->getAttrib('readonly')) ||
                ($element->helper == 'Button') ||
                ($element->helper == 'formSubmit') ||
                ($element->helper == 'SubmitButton')) {
                return null;
            }
            return $element->getId();

        } elseif (($element instanceof \Zend_Form) ||
                  ($element instanceof \Zend_Form_DisplayGroup)) {
            foreach ($element as $subelement) {
                if ($focus = $this->_getFocus($subelement)) {
                    return $focus;
                }
            }
        }

        return null;
    }

    /**
     * Render form elements
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $form  = $this->getElement();
        $view  = $form->getView();
        $request = \MUtil\Controller\Front::getRequest();

        $focus = $request->getParam($form->focusTrackerElementId) ?: $this->_getFocus($form);

        $allowedElements = [];
        $this->_getAllowed($form, $allowedElements);
        
        if (in_array($focus, $allowedElements)) {
            if ($form->focusTrackerElementId) {
                $form->getElement($form->focusTrackerElementId)->setValue($focus);
            }

            if (($view !== null) && ($focus !== null)) {
                // Use try {} around e.select as nog all elements have a select() function
                $script = "e = document.getElementById('$focus');";
                $script .= "
                    if (e) {
                        e.focus();
                        if (e.scrollIntoView) {
                            e.scrollIntoView({behavior: 'smooth', block: 'center'});
                        }
                        try {
                            if (e.select) {
                                e.select();
                            }
                        } catch (ex) {}
                    }";

                $view->inlineScript()->appendScript($script);
            }
        }
        
        return $content;
    }
}
