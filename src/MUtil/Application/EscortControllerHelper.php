<?php

/**
 *
 * @package    MUtil
 * @subpackage Application
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Application;

/**
 * @package    MUtil
 * @subpackage Application
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class EscortControllerHelper extends \Zend_Controller_Action_Helper_Abstract
{
    /**
     *
     * @var \MUtil\Application\Escort
     */
    private $_escort;

    /**
     *
     * @param \MUtil\Application\Escort $escort
     */
    public function __construct(\MUtil\Application\Escort $escort)
    {
        $this->setEscort($escort);
    }

    /**
     *
     * @return \MUtil\Application\Escort
     */
    public function getEscort()
    {
        return $this->_escort;
    }

    /**
     * Hook into action controller initialization
     *
     * @return void
     */
    public function init()
    {
        $this->_escort->controllerInit($this->getActionController());
    }

    /**
     * Hook into action controller preDispatch() workflow
     *
     * @return void
     */
    public function preDispatch()
    {
        $this->_escort->controllerBeforeAction($this->getActionController());
    }

    /**
     * Hook into action controller postDispatch() workflow
     *
     * @return void
     */
    public function postDispatch()
    {
        $this->_escort->controllerAfterAction($this->getActionController());
    }

    /**
     * Register escort as a controller helper.
     *
     * @param  \MUtil\Application\Escort $escort
     * @return self
     */
    public static function register(\MUtil\Application\Escort $escort)
    {
        $helper = new self($escort);

        \Zend_Controller_Action_HelperBroker::addHelper($helper);

        return $helper;
    }

    /**
     * setActionController()
     *
     * @param  \Zend_Controller_Action $actionController
     * @return \Zend_Controller_ActionHelper_Abstract Provides a fluent interface
     */
    public function setActionController(\Zend_Controller_Action $actionController = null)
    {
        $result = parent::setActionController($actionController);

        $this->_escort->setActionController($actionController);

        return $result;
    }

    /**
     *
     * @param \MUtil\Application\Escort $escort
     * @return \MUtil\Application\EscortControllerHelper
     */
    public function setEscort(\MUtil\Application\Escort $escort)
    {
        $this->_escort = $escort;

        return $this;
    }
}