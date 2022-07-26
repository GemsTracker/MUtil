<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Lazy_Stack
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Lazy\Stack;

/**
 * There is no stack, throw errors when used
 *
 * Defines a source for variable values in a lazy evaluation.
 *
 * @package    MUtil
 * @subpackage Lazy_Stack
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
class EmptyStack implements \MUtil\Lazy\StackInterface
{
    /**
     * @private A string describing where this object was created.
     */
    private $_source;

    /**
     * The constructor can be used to set a source name.
     *
     * Debugging lazy stuff is hard enough, so we can use all the easy help we can get.
     *
     * @param string $source An optional source name to specify where this stack was created.
     */
    public function __construct($source = null)
    {
        $this->_source = $source;
    }

    /**
     * Returns a value for $name
     *
     * @param string $name A name indentifying a value in this stack.
     * @return A value for $name
     */
    public function lazyGet($name)
    {
        if ($this->_source) {
            throw new \MUtil\Lazy\LazyException("No lazy stack defined when called from '$this->_source', but asked for '$name' parameter.");
        } else {
            throw new \MUtil\Lazy\LazyException("No lazy stack defined, but asked for '$name' parameter.");
        }
    }
}
