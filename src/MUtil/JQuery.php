<?php

/**
 * Copyright (c) 2011, Erasmus MC
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * Neither the name of Erasmus MC nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Gems
 * @subpackage JQuery
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @version    $Id$
 */

/**
 *
 * @package    MUtil
 * @subpackage JQuery
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_JQuery extends \ZendX_JQuery
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
        if (false === $view->getPluginLoader('helper')->getPaths('MUtil_JQuery_View_Helper')) {
            $view->addHelperPath('ZendX/JQuery/View/Helper', 'ZendX_JQuery_View_Helper');
            $view->addHelperPath('MUtil/JQuery/View/Helper', 'MUtil_JQuery_View_Helper');
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
            $helper = new \MUtil_JQuery_View_Helper_JQuery();
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
            return false !== $object->getPluginLoader('helper')->getPaths('MUtil_JQuery_View_Helper');
        }

        if ($object instanceof \Zend_Form) {
            return false !== $object->getPluginLoader(\Zend_Form::DECORATOR)->getPaths('MUtil_JQuery_Form_Decorator');
        }

        if (is_object($object))  {
            throw new \ZendX_JQuery_Exception('Checking for JQuery on invalid object of class: ' . get_class($object));
        } else {
            throw new \ZendX_JQuery_Exception('Checking for JQuery on non-object');
        }
    }
}
