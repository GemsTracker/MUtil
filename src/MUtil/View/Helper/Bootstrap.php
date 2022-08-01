<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Bootstrap
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\View\Helper;

/**
 *
 * @package    MUtil
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class Bootstrap extends \Zend_View_Helper_Abstract
{
    /**
     * @var \MUtil\View\Helper\Bootstrapper
     */
    private $_bootstrapper;

   /**
     * Initialize helper
     *
     * Retrieve bootstrapper from registry or create new container and store in
     * registry.
     *
     * @return void
     */
    public function __construct()
    {
        $registry = \Zend_Registry::getInstance();
        if (!isset($registry[__CLASS__])) {
            $container = new Bootstrapper();
            $registry[__CLASS__] = $container;
        }
        $this->_bootstrapper = $registry[__CLASS__];
    }

    public function bootstrap()
    {
        return $this->_bootstrapper;
    }

    /**
     * Set view to this class and the bootstrapper
     *
     * @param  \Zend_View_Interface $view
     * @return void
     */
    public function setView(\Zend_View_Interface $view)
    {
        $this->view = $view;
        $this->_bootstrapper->setView($view);
    }
}
