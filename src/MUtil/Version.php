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
    const MAJOR = 2;
    const MINOR = 0;
    const BUILD = 100;

    public static function get()
    {
        return self::MAJOR . '.' . self::MINOR . '.' . self::BUILD;
    }
}
