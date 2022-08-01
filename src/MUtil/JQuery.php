<?php

/**
 *
 * @package    Gems
 * @subpackage JQuery
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil;

/**
 *
 * @package    MUtil
 * @subpackage JQuery
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class JQuery extends \ZendX_JQuery
{
    static $jquery;

    /**
     * jQuery-enable a form instance
     *
     * @param  \Zend_Form $form
     * @return void
     */
    public static function enableForm(\Zend_Form $form)
    {
        $form->addPrefixPath('ZendX_JQuery_Form_Decorator', 'ZendX/JQuery/Form/Decorator', 'decorator')
             ->addPrefixPath('ZendX_JQuery_Form_Element', 'ZendX/JQuery/Form/Element', 'element')
             ->addElementPrefixPath('ZendX_JQuery_Form_Decorator', 'ZendX/JQuery/Form/Decorator', 'decorator')
             ->addDisplayGroupPrefixPath('ZendX_JQuery_Form_Decorator', 'ZendX/JQuery/Form/Decorator');

        foreach ($form->getSubForms() as $subForm) {
            self::enableForm($subForm);
        }

        if (null !== ($view = $form->getView())) {
            self::enableView($view);
        }
    }


    /**
     * jQuery-enable a view instance
     *
     * @param  \Zend_View_Interface $view
     * @return void
     */
    public static function enableView(\Zend_View_Interface $view)
    {
        if (false === $view->getPluginLoader('helper')->getPaths('ZendX_JQuery_View_Helper')) {
            $view->addHelperPath('ZendX/JQuery/View/Helper', 'ZendX_JQuery_View_Helper');
        }
    }

    /**
     * Returns the jQuery container object assigned to the view helper.
     *
     * @staticvar \ZendX_JQuery_View_Helper_JQuery_Container $jquery
     * @return \ZendX_JQuery_View_Helper_JQuery_Container
     */
    public static function jQuery()
    {
        static $jquery;

        if (! $jquery) {
            $helper = new \MUtil\View\Helper\JQuery();
            $jquery = $helper->jQuery();
        }

        return $jquery;
    }

    /**
     * Check if the view or form is using JQuery
     *
     * @param mixed $object \Zend_View_Abstract or \Zend_Form
     * @return boolean
     */
    public static function usesJQuery($object)
    {
        if ($object instanceof \Zend_View_Abstract) {
            return false !== $object->getPluginLoader('helper')->getPaths('MUtil\JQuery_View_Helper');
        }

        if ($object instanceof \Zend_Form) {
            return false !== $object->getPluginLoader(\Zend_Form::DECORATOR)->getPaths('MUtil\JQuery_Form_Decorator');
        }

        if (is_object($object))  {
            throw new \ZendX_JQuery_Exception('Checking for JQuery on invalid object of class: ' . get_class($object));
        } else {
            throw new \ZendX_JQuery_Exception('Checking for JQuery on non-object');
        }
    }
}
