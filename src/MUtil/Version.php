<?php

/**
 *
 * @package    MUtil
 * @subpackage Version
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil;

/**
 * \MUtil version info
 *
 * @package    MUtil
 * @subpackage Util
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Version
{
    const MAJOR = 1;
    const MINOR = 9;
    const BUILD = 66;

    public static function get()
    {
        return self::MAJOR . '.' . self::MINOR . '.' . self::BUILD;
    }
}
