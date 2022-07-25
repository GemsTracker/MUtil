<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Lazy_Stack
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Lazy\Stack;

/**
 * Get a simple array stack implemenation
 *
 * @package    MUtil
 * @subpackage ArrayStack
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class ArrayStack extends \ArrayObject implements \MUtil\Lazy\StackInterface
{
    /**
     * Should we throw an exception on a missing value?
     *
     * @var boolean
     */
    private $_throwOnMiss = false;

    /**
     * Returns a value for $name
     *
     * @param string $name A name indentifying a value in this stack.
     * @return A value for $name
     */
    public function lazyGet($name)
    {
        // \MUtil\EchoOut\EchoOut::track($name, $this->offsetExists($name), $this->offsetGet($name), $this->getArrayCopy());
        if ($this->offsetExists($name)) {
            return $this->offsetGet($name);
        }
        if ($this->_throwOnMiss) {
            throw new \MUtil\Lazy\LazyException("No lazy stack variable defined for '$name' parameter.");
        }
        if (\MUtil\Lazy::$verbose) {
            \MUtil\EchoOut\EchoOut::header("No lazy stack variable defined for '$name' parameter.");
        }

        return null;
    }

    /**
     * Should we throw an exception on a missing value?
     *
     * @var boolean
     */

    /**
     * Set this stack to throw an exception
     *
     * @param mixed $throw boolean
     * @return \MUtil_ArrayStack (continuation pattern_
     */
    public function setThrowOnMiss($throw = true)
    {
        $this->_throwOnMiss = $throw;
        return $this;
    }
}
