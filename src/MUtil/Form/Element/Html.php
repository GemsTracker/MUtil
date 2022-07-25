<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Form\Element;

/**
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
class Html extends \Zend_Form_Element_Xhtml implements \MUtil\Form\Element\NoFocusInterface
{
    /**
     * Default view helper to use
     * @var string
     */
    public $helper = 'html';

    /**
     * Overloading: allow rendering specific decorators
     *
     * Call renderDecoratorName() to render a specific decorator.
     *
     * @param  string $method
     * @param  array $args
     * @return \MUtil\Html\HtmlElement or at least something that implements the \MUtil\Html\HtmlInterface interface
     * @throws \Zend_Form_Exception for invalid decorator or invalid method call
     */
    public function __call($method, $args)
    {
        if ('render' == substr($method, 0, 6)) {
            return parent::__call($method, $args);
        }

        $elem = \MUtil\Html::createArray($method, $args);

        $value = $this->getValue();

        if (! $value instanceof \MUtil\Html\ElementInterface) {
            $value = new \MUtil\Html\Sequence();
        }
        $value->append($elem);
        $this->setValue($value);

        return $elem;
    }

    /**
     * The HTML element is always valid and we don't want the value to be changed by this function
     */
    public function isValid($value, $context = null) {
        return true;
    }
}
