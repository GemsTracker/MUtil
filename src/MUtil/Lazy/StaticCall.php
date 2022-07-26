<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Lazy
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Lazy;

/**
 * While not Lazy of itself this object makes it easy to make a static call
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class StaticCall
{
    protected $_className;

    /**
     * Return a lazy version of the call
     *
     * @return \MUtil\Lazy\Call
     */
    public function __call($name, array $arguments)
    {
        return new \MUtil\Lazy\Call(array($this->_className, $name), $arguments);
    }

    public function  __construct($className)
    {
        $this->_className = $className;
    }

    /**
     * Return a callable for this function
     *
     * @return \MUtil\Lazy\Property
     */
    public function __get($name)
    {
        // \MUtil\EchoOut\EchoOut::r($this->_className . '::' . $name);
        return array($this->_className, $name);
        // return $this->_className . '::' . $name;
    }
}
