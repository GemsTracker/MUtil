<?php

namespace MUtilTest;

use PHPUnit\Framework\TestCase;

/**
 *
 *
 * @package    MUtil
 * @subpackage Dec
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Unit test for class MUtil\StringUtil\StringUtil
 *
 * @package    MUtil
 * @subpackage Dec
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.7
 */
class DecTest extends TestCase
{
    /**
     * Dataprovider
     *
     * @return array
     */
    public static function forCeiling()
    {
        return [
            [10.49825, 1, 10.5],
            [10.99825, 1, 11.0],
            [10.09825, 1, 10.1],
            [10.09825, 2, 10.10],
            [10.09825, 3, 10.099],
            [79.99*100, 0, 7999],
            [79.99*100, -1, 8000],
            [(10.02-10)*100, 1, 2.0],
        ];
    }

    /**
     * Dataprovider
     *
     * @return array
     */
    public static function forFloor()
    {
        return [
            [10.49825, 1, 10.4],
            [10.99825, 1, 10.9],
            [10.09825, 1, 10.0],
            [10.09825, 2, 10.09],
            [10.09825, 3, 10.098],
            [79.99*100, 0, 7999],
            [79.99*100, -1, 7990],
            [(10.02-10)*100, 1, 2.0],
        ];
    }

    /**
     *
     * @dataProvider forCeiling
     * @param float $float
     * @param int $precision
     * @param float $output
     */
    public function testCeil($float, $precision, $output)
    {
        $this->assertEquals($output, \MUtil\Dec::ceil($float, $precision));
    }

    /**
     *
     * @dataProvider forFloor
     * @param float $float
     * @param int $precision
     * @param float $output
     */
    public function testFloor($float, $precision, $output)
    {
        $this->assertEquals($output, \MUtil\Dec::floor($float, $precision));
    }
}
