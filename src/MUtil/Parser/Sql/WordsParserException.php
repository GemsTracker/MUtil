<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Parser_Sql
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Parser\Sql;

include_once 'Zend/Exception.php';

/**
 *
 * @package    MUtil
 * @subpackage Parser_Sql
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class WordsParserException extends \Zend_Exception
{
    public function __construct($what, $line, $char)
    {
        parent::__construct("Parse error:\n\n\t$what\n\nStarting at line $line, character $char.");
    }

}
