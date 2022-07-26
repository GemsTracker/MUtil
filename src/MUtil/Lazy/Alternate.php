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
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Alternate extends \MUtil\Lazy\LazyAbstract
{
    private $_values;
    private $_count;
    private $_current;

    public function __construct(array $values)
    {
        $this->_values  = array_values($values);
        $this->_count   = count($values);
        $this->_current = 0;

        if (0 == $this->_count) {
            throw new \MUtil\Lazy\LazyException('Class ' . __CLASS__ . ' needs at least one value as an argument.');

        } elseif (1 == $this->_count) {
            $this->_count++;
            $this->_values[] = null;
        }
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
        if ($this->_current >= $this->_count) {
            $this->_current = 0;
        }

        return $this->_values[$this->_current++];
    }
}
