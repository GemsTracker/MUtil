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
 *
 * @package    MUtil
 * @subpackage Task
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @version    $Id: Soruce.php$
 */

/**
 * Standard Source for \MUtil_Registry_TargetInterface objects.
 *
 * The source can be loaded with multiple objects or array's and
 * the public properties and the keys of an array are used as
 * sourced for the named variables requested by the target.
 *
 * This allows sources of values, e.g. the \Zend_Registry, to be injected
 * automatically in a Target Object by calling $this->applySource().
 *
 * @see \MUtil_Registry_TargetInterface
 *
 * @package    MUtil
 * @subpackage Registry
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.1
 */
class MUtil_Registry_Source implements \MUtil_Registry_SourceInterface
{
    /**
     * An array of container objects, each containing resources.
     *
     * @var array
     */
    protected $_containers = array();

    /**
     * Debugging variable
     *
     * @var boolean When true echo is used
     */
    public static $verbose = false;

    /**
     * Initializes the Source.
     *
     * @param mixed $container1 First container, if not specified, then the \Zend_Registry, otherwise any object will do.
     * @param mixed $container2 Optional extra containers.
     */
    public function  __construct($container1 = null, $container2 = null)
    {
        $containers = func_get_args();

        if (null === $container1) {
            $containers[0] = \Zend_Registry::getInstance();
        }

        foreach ($containers as $container) {
            $this->addRegistryContainer($container);
        }
    }

    /**
     *
     * @param \MUtil_Registry_TargetInterface $target
     * @param string $name
     * @return boolean A correct match was found
     */
    protected function _applySourceContainers(\MUtil_Registry_TargetInterface $target, $name)
    {
        foreach ($this->_containers as $container) {
            if (isset($container->$name)) {
                $resource = $container->$name;
                if ($target->answerRegistryRequest($name, $resource)) {
                    if (self::$verbose) {
                        \MUtil_Echo::r('Resource set: ' . get_class($target) . '->' . $name .
                                ' type "' . (is_object($resource) ? get_class($resource) : gettype($resource)) .
                                '" from ' . get_class($container));
                    }
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Adds an extra source container to this object.
     *
     * @param mixed $container \Zend_Config, array or \ArrayObject
     * @param string $name An optional name to identify the container
     * @return \MUtil_Registry_Source
     */
    public function addRegistryContainer($container, $name = null)
    {
        if ($container instanceof \Zend_Config) {
            $container = $container->toArray();
        }
        if (is_array($container)) {
            $container = new \ArrayObject($container);
        }
        if ($container instanceof \ArrayObject) {
            $container->setFlags(\ArrayObject::ARRAY_AS_PROPS);
        }

        // Always append in reverse order
        if (null === $name) {
            array_unshift($this->_containers, $container);
        } else {
            $this->_containers = array($name => $container) + $this->_containers;
        }

        return $this;
    }

    /**
     * Apply this source to the target.
     *
     * @param \MUtil_Registry_TargetInterface $target
     * @return boolean True if $target is OK with loaded requests
     */
    public function applySource(\MUtil_Registry_TargetInterface $target)
    {
        foreach ($target->getRegistryRequests() as $name) {
            if (! $this->_applySourceContainers($target, $name)) {
                if (self::$verbose) {
                    \MUtil_Echo::r('Missed resource: ' . get_class($target) . '->' . $name);
                } /* else {
                echo '<br/>missed ' . $name . "\n";
                } // */
            }
        }

        $result = $target->checkRegistryRequestsAnswers();

        $target->afterRegistry();

        return $result;
    }

    /**
     * Removes a source container from this object.
     *
     * @param string $name The name to identify the container
     * @return \MUtil_Registry_Source
     */
    public function removeRegistryContainer($name)
    {
        unset($this->_containers[$name]);

        return $this;
    }
}