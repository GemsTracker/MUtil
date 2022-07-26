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

class LazyException extends \Zend_Exception
{
    /**
     *
     * @var type
     * /
    private $_stacktrace;

    /**
     * Construct the exception
     *
     * @param  string $msg
     * @param  int $code
     * @param  \Exception $previous
     * @param  array $stacktrace
     * @return void
     */
    public function __construct($msg = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($msg, $code, $previous);

        /*
        $this->_stacktrace = debug_backtrace(false);

        // Remove this line
        array_shift($this->_stacktrace);
        // */
    }
}