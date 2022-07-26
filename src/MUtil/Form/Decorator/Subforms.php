<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Form_Decorator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Form\Decorator;

/**
 *
 * @package    MUtil
 * @subpackage Form_Decorator
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class Subforms extends \Zend_Form_Decorator_Abstract
{
    /**
     * Render the element
     *
     * @param  string $content Content to decorate
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        if ((null === $element) ||
            (null === ($view = $element->getView()))) {
            return $content;
        }

        if ($element instanceof \MUtil\Form\Element\SubForms) {
            $subforms = $element->getSubForms();
        } elseif ($element instanceof \Zend_Form)  {
            $subforms = array($element);
        } else {
            $subforms = array();
        }

        foreach ($subforms as $subform) {
            foreach ($subform->getElements() as $subelement) {
                if ($subelement instanceof \Zend_Form_Element_Hidden) {
                    $subelement->clearDecorators();
                    $subelement->addDecorator('ViewHelper');
                    $content .= $subelement->render($view) . "\n";

                } elseif (($subelement instanceof \Zend_Form_Element) ||
                        ($subelement instanceof \Zend_Form_DisplayGroup) ||
                        ($subelement instanceof \Zend_Form)) {
                    $content .= $subelement->render($view) . "\n";
                    
                }
            }
        }

        return $content;
    }
}
