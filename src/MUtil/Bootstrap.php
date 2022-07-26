<?php

/**
 *
 * @package    MUtil
 * @subpackage Bootstrap
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil;

/**
 * Bootstrap (and less) enable an application
 *
 * @package    MUtil
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class Bootstrap
{
    /**
     * Current default supported Bootstrap library version with \MUtil\Bootstrap
     *
     * @const string
     */
    // const DEFAULT_BOOTSTRAP_VERSION = "4.2.1"; // This version breaks working of code
    const DEFAULT_BOOTSTRAP_VERSION = "3.4.1";

    const DEFAULT_FONTAWESOME_VERSION = '4.2.0';

    /**
     * Link to CDN http://www.bootstrapcdn.com/
     *
     * @const string
     */
    const CDN_BASE = '//maxcdn.bootstrapcdn.com/bootstrap/';

    const CDN_FONTAWESOME_BASE = '//maxcdn.bootstrapcdn.com/font-awesome/';

    /**
     * Location of bootstrap CSS
     *
     * @const string
     */
    const CDN_CSS = '/css/bootstrap.min.css';

    const CDN_FONTAWESOME_CSS = '/css/font-awesome.min.css';
    /**
     * Location of bootstrap JavaScript
     *
     * @const string
     */
    const CDN_JS = '/js/bootstrap.min.js';

    /**
     * Bootsrap view helper
     *
     * @var \MUtil\Bootstrap\View\Helper\Bootstrap
     */
    private static $_bootstrap;

    public static $fontawesome = false;

    /**
     * Returns the Bootstrapper object assigned to the view helper.
     *
     * @staticvar \MUtil\Bootstrap\View\Helper\Bootstrapper $bootstrap
     * @return \MUtil\Bootstrap\View\Helper\Bootstrapper
     */
    public static function bootstrap($options=array())
    {
        if (self::$_bootstrap) {
            return self::$_bootstrap;
        }
        if (isset($options['fontawesome']) && $options['fontawesome'] === true) {
            self::$fontawesome = true;
        }

        return false;
    }

    /**
     * jQuery-enable a form instance
     *
     * @param  \Zend_Form $form
     * @return void
     * /
    public static function enableForm(\Zend_Form $form)
    {
        $form->addPrefixPath('MUtil\Bootstrap_Form_Decorator', 'MUtil/Bootstrap/Form/Decorator', 'decorator')
             ->addPrefixPath('MUtil\Bootstrap_Form_Element', 'MUtil/Bootstrap/Form/Element', 'element')
             ->addElementPrefixPath('MUtil\Bootstrap_Form_Decorator', 'MUtil/Bootstrap/Form/Decorator', 'decorator')
             ->addDisplayGroupPrefixPath('MUtil\Bootstrap_Form_Decorator', 'MUtil/Bootstrap/Form/Decorator');

        foreach ($form->getSubForms() as $subForm) {
            self::enableForm($subForm);
        }

        if (null !== ($view = $form->getView())) {
            self::enableView($view);
        }
    }

    /**
     * Bootstrap-enable a view instance
     *
     * @param  \Zend_View_Interface $view
     * @return void
     */
    public static function enableView(\Zend_View_Interface $view)
    {
        if (! \MUtil\JQuery::usesJQuery($view)) {
            \MUtil\JQuery::enableView($view);
        }

        if (false === $view->getPluginLoader('helper')->getPaths('MUtil\Bootstrap_View_Helper')) {
            $view->addHelperPath('MUtil/Bootstrap/View/Helper', 'MUtil\Bootstrap_View_Helper');
        }
        self::$_bootstrap = $view->bootstrap();
    }

    /**
     * Is bootstrap enabled?
     *
     * @return boolean
     */
    public static function enabled()
    {
        return self::$_bootstrap instanceof \MUtil\Bootstrap\View\Helper\Bootstrapper;
    }

    /**
     * Check if the view or form is using Bootstrap
     *
     * @param mixed $object \Zend_View_Abstract or \Zend_Form
     * @return boolean
     */
    public static function usesBootstrap($object)
    {
        if ($object instanceof \Zend_View_Abstract) {
            return false !== $object->getPluginLoader('helper')->getPaths('MUtil\Bootstrap_View_Helper');
        }

        /*
        if ($object instanceof \Zend_Form) {
            return false !== $object->getPluginLoader(\Zend_Form::DECORATOR)->getPaths('ZendX_JQuery_Form_Decorator');
        } // */

        if (is_object($object))  {
            throw new \MUtil\Bootstrap\BootstrapException(
                    'Checking for Bootstrap on invalid object of class: ' . get_class($object)
                    );
        } else {
            throw new \MUtil\Bootstrap\BootstrapException('Checking for Bootstrap on non-object');
        }
    }
}
