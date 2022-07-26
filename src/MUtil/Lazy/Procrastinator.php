<?php

/**
 *
 * @package    MUtil
 * @subpackage Lazy
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Lazy;

/**
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface Procrastinator
{
    /**
     * Returns a lazy instance of item. Do NOT use MUtil\Lazy::L() in this function!!!
     *
     * @return \MUtil\Lazy\LazyInterface
     */
    public function toLazy();
}