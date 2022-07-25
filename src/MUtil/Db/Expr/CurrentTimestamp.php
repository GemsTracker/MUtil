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
 * Standard current timestamp expression
 *
 * @package    MUtil
 * @subpackage Db
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.1.36
 */
class CurrentTimestamp extends \Zend_Db_Expr
{
    /**
     * Instantiate teh current timestamp expression.
     */
    public function __construct()
    {
        parent::__construct('CURRENT_TIMESTAMP');
    }
}
