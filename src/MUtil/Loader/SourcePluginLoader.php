<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Loader
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Loader;

/**
 * Plugin loader that applies a source when loading
 *
 * @package    MUtil
 * @subpackage Loader
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.6.3
 */
class SourcePluginLoader extends \MUtil\Loader\PluginLoader
{
    /**
     *
     * @var \MUtil\Registry\SourceInterface
     */
    protected $_source;

    /**
     * Show warning when source not set.
     *
     * @var boolean
     */
    public static $verbose = false;

    /**
     * Instantiate a new class using the arguments array for initiation
     *
     * @param string $className
     * @param array $arguments Instanciation arguments
     * @return className
     */
    public function createClass($className, array $arguments = array())
    {
        $object = parent::createClass($className, $arguments);
        if ($object instanceof \MUtil\Registry\TargetInterface) {
            if ($this->_source instanceof \MUtil\Registry\SourceInterface) {
                $this->_source->applySource($object);
            } elseif (self::$verbose) {
                \MUtil\EchoOut\EchoOut::r("Loading target class $className, but source not set.");
            }
        }

        return $object;
    }

    /**
     * Get the current source for the loader (if any)
     *
     * @return \MUtil\Registry\SourceInterface
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Is there a source for the loader
     *
     * @return boolean
     */
    public function hasSource()
    {
        return $this->_source instanceof \MUtil\Registry\SourceInterface;
    }

    /**
     * Set the current source for the loader
     *
     * @param \MUtil\Registry\SourceInterface $source
     * @return \MUtil\Loader\SourcePluginLoader (continuation pattern)
     */
    public function setSource(\MUtil\Registry\SourceInterface $source)
    {
        $this->_source = $source;
        return $this;
    }
}
