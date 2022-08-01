<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage View\Helper
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\View\Helper;

/**
 *
 * @package    MUtil
 * @subpackage View\Helper
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class JQuery extends \ZendX_JQuery_View_Helper_JQuery
{
   /**
     * Initialize helper
     *
     * Retrieve container from registry or create new container and store in
     * registry.
     *
     * @return void
     */
    public function __construct()
    {
        $registry = \Zend_Registry::getInstance();
        if (!isset($registry[__CLASS__])) {
            $container = new \MUtil\View\Helper\Container();
            $registry[__CLASS__] = $container;
        }
        $this->_container = $registry[__CLASS__];
    }
}
