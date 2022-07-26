<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Registry
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Registry;

/**
 * The TargetInterface is a lightweight dependency injection framework that enables an
 * object to tell which central variables can/must be set.
 *
 * This allows sources containing variables, e.g. the \Zend_Registry, to have their values
 * automatically injected into the TargetObject.
 *
 * @see \MUtil\Registry\Source
 *
 * @package    MUtil
 * @subpackage Registry
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
interface TargetInterface
{
    /**
     * Called after the check that all required registry values
     * have been set correctly has run.
     *
     * @return void
     */
    public function afterRegistry();

    /**
     * Allows the source to set request.
     *
     * @param string $name Name of resource to set
     * @param mixed $resource The resource.
     * @return boolean True if $resource was OK
     */
    public function answerRegistryRequest($name, $resource);

    /**
     * Should be called after answering the request to allow the Target
     * to check if all required registry values have been set correctly.
     *
     * @return boolean False if required values are missing.
     */
    public function checkRegistryRequestsAnswers();

    /**
     * Allows the loader to know the resources to set.
     *
     * @return array of string names
     */
    public function getRegistryRequests();
}
