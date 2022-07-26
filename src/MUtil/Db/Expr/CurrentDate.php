<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Db
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Db\Expr;

/**
 * Standard current date expression
 *
 * @package    MUtil
 * @subpackage Db
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.3
 */
class CurrentDate extends \Zend_Db_Expr
{
    /**
     * Instantiate teh current timestamp expression.
     */
    public function __construct()
    {
        parent::__construct('CURRENT_DATE');
    }
}
