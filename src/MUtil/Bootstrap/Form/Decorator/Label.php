<?php

namespace MUtil\Form\Decorator;

class Label extends \Zend_Form_Decorator_Label
{
    /**
     * Get label to render
     *
     * @return string
     */
    public function getLabel()
    {
        if (null === ($element = $this->getElement())) {
            return '';
        }

        $label = $element->getLabel();
        if ($label !== null) {
            $label = trim($label);
        }

        if (empty($label)) {
            return '';
        }

        $optPrefix = $this->getOptPrefix();
        $optSuffix = $this->getOptSuffix();
        $reqPrefix = $this->getReqPrefix();
        $reqSuffix = $this->getReqSuffix();
        $separator = $this->getSeparator();

        if (!empty($label)) {
            if ($element->isRequired()) {
                $label = $reqPrefix . $label . $reqSuffix;
            } else {
                $label = $optPrefix . $label . $optSuffix;
            }
        }

        return $label;
    }
}