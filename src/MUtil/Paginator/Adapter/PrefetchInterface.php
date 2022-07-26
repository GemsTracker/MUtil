<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Paginator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 201e Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Paginator\Adapter;

/**
 * A marker interface to suggest it is better to prefetch the result items before
 * getting the total as this may be faster than the other way around.
 *
 * @package    MUtil
 * @subpackage Paginator
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
interface PrefetchInterface extends \Zend_Paginator_Adapter_Interface
{}
