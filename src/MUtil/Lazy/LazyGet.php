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
 * Creates a lazy get to returns a parameter set in the $stack used in \MUtil\Lazy::rise().
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1
 */
class LazyGet extends \MUtil\Lazy\LazyAbstract
{
    /**
     * The name of the stack value to get
     *
     * @var string
     */
    private $_name;

    /**
     *
     * @param string $name The name of the stack value to get
     */
    public function __construct($name)
    {
        $this->_name = $name;
    }

    /**
    * The functions that fixes and returns a value.
    *
    * Be warned: this function may return a lazy value.
    *
    * @param \MUtil\Lazy\StackInterface $stack A \MUtil\Lazy\StackInterface object providing variable data
    * @return mixed
    */
    public function __toValue(\MUtil\Lazy\StackInterface $stack)
    {
        return $stack->lazyGet($this->_name);
    }
}
