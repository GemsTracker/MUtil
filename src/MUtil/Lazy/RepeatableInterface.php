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
 * An object implementing the RepeatableInterface can be called
 * repeatedly and sequentially with the content of the properties,
 * function calls and array access methods changing until each
 * value of a data list has been returned.
 *
 * This interface allows you to specify an action only once instead
 * of repeatedly in a loop.
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface RepeatableInterface extends \Zalt\Late\RepeatableInterface
{
}
