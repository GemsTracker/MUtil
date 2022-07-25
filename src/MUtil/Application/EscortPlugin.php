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
 *
 * @package    MUtil
 * @subpackage Application
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class EscortPlugin extends \Zend_Controller_Plugin_Abstract
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
     * Called before \Zend_Controller_Front exits its dispatch loop.
     *
     * @return void
     */
    public function dispatchLoopShutdown()
    {
        $this->_escort->dispatchLoopShutdown();
    }

    /**
     * Called before \Zend_Controller_Front enters its dispatch loop.
     *
     * @param  \Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(\Zend_Controller_Request_Abstract $request)
    {
        $this->_escort->dispatchLoopStartup($request);
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
     * Called after an action is dispatched by \Zend_Controller_Dispatcher.
     *
     * This callback allows for proxy or filter behavior. By altering the
     * request and resetting its dispatched flag (via
     * {@link \Zend_Controller_Request_Abstract::setDispatched() setDispatched(false)}),
     * a new action may be specified for dispatching.
     *
     * @param  \Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch(\Zend_Controller_Request_Abstract $request)
    {
        $this->_escort->postDispatch($request);
    }

    /**
     * Called before an action is dispatched by \Zend_Controller_Dispatcher.
     *
     * This callback allows for proxy or filter behavior.  By altering the
     * request and resetting its dispatched flag (via
     * {@link \Zend_Controller_Request_Abstract::setDispatched() setDispatched(false)}),
     * the current action may be skipped.
     *
     * @param  \Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(\Zend_Controller_Request_Abstract $request)
    {
        $this->_escort->preDispatch($request);
    }

    /**
     * Register escort as a frontcontroller plugin.
     *
     * @param  \MUtil\Application\Escort $escort
     * @param  int $stackIndex Optional; stack index for plugin
     * @return self
     */
    public static function register(\MUtil\Application\Escort $escort, $stackIndex = null)
    {
        $plugin = new self($escort);
        $front = \Zend_Controller_Front::getInstance();

        $front->registerPlugin($plugin, $stackIndex);

        return $plugin;
    }

    /**
     * Called after \Zend_Controller_Router exits.
     *
     * Called after \Zend_Controller_Front exits from the router.
     *
     * @param  \Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeShutdown(\Zend_Controller_Request_Abstract $request)
    {
        $this->_escort->routeShutdown($request);
    }

    /**
     * Called before \Zend_Controller_Front begins evaluating the
     * request against its routes.
     *
     * @param \Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function routeStartup(\Zend_Controller_Request_Abstract $request)
    {
        $this->_escort->routeStartup($request);
    }

    /**
     *
     * @param \MUtil\Application\Escort $escort
     * @return \MUtil\Application\EscortPlugin
     */
    public function setEscort(\MUtil\Application\Escort $escort)
    {
        $this->_escort = $escort;

        return $this;
    }

    /**
     * Set request object, both for this and the boostrap class.
     *
     * If the bootstrap class has a setRequest method it is set.
     *
     * @param \Zend_Controller_Request_Abstract $request
     * @return \Zend_Controller_Plugin_Abstract
     */
    public function setRequest(\Zend_Controller_Request_Abstract $request)
    {
        $this->_escort->setRequest($request);

        return parent::setRequest($request);
    }

    /**
     * Set response object, both for this and the boostrap class.
     *
     * If the bootstrap class has a setResponse method it is set.
     *
     * @param \Zend_Controller_Response_Abstract $response
     * @return \Zend_Controller_Plugin_Abstract
     */
    public function setResponse(\Zend_Controller_Response_Abstract $response)
    {
        $this->_escort->setResponse($response);

        return parent::setResponse($response);
    }

}