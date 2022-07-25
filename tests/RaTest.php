<?php

namespace MUtilTest;

use PHPUnit\Framework\TestCase;

/**
 * Unit test for class MUtil\Ra
 *
 * @author     Michiel Rook <info@touchdownconsulting.nl>
 * @package    MUtil
 * @subpackage Ra
 */
class RaTest extends TestCase
{
    protected $_columnTest = [
        'r1' => ['c1' => 1, 'c2' => 'c1'],
        'r2' => ['c1' => 2, 'c2' => 'c2'],
        'r3' => ['c1' => 3],
    ];

    public function testArgsDouble()
    {
        $args = \MUtil\Ra::args([['a' => 'b'], ['a' => 'c']]);
        $this->assertEquals($args, ['a' => 'c']);
    }

    public function testArgsSkipOrName()
    {
        $args = \MUtil\Ra::args([0 => [0 => 'f', 1 => ['o' => '0', 0 => 'b']], 1 => ['a' => ['r' => 'r']]], 1);
        $this->assertEquals($args, ['a' => ['r' => 'r']]);
    }

    public function testArgsDefaults()
    {
        $args = \MUtil\Ra::args(['r1'], ['class1', 'class2'], ['class1' => 'odd', 'class2' => 'even']);
        $this->assertEquals($args, ['class1' => 'r1', 'class2' => 'even']);
    }

    public function testBraceKeys()
    {
        $args = \MUtil\Ra::braceKeys([0 => 'a', 'b' => 'c'], '{', '}');
        $this->assertEquals($args, ['{0}' => 'a', '{b}' => 'c']);
    }

    public function testBraceKeysLeftOnly()
    {
        $args = \MUtil\Ra::braceKeys([0 => 'a', 'b' => 'c'], '"');
        $this->assertEquals($args, ['"0"' => 'a', '"b"' => 'c']);
    }

    public function testColumnRelaxed()
    {
        $args = \MUtil\Ra::column('c2', $this->_columnTest, \MUtil\Ra::RELAXED);
        $this->assertEquals($args, ['r1' => 'c1', 'r2' => 'c2']);
    }

    public function testColumnRelaxedEmpty()
    {
        $args = \MUtil\Ra::column('c3', $this->_columnTest, \MUtil\Ra::RELAXED);
        $this->assertEmpty($args);
    }

    public function testColumnRelaxedSkips()
    {
        $args = \MUtil\Ra::column('c2', $this->_columnTest, \MUtil\Ra::RELAXED);
        $this->assertNotContains('r3', array_keys($args));
    }

    public function testColumnStrict()
    {
        $args = \MUtil\Ra::column('c2', $this->_columnTest, \MUtil\Ra::STRICT);
        $this->assertEquals($args, ['r1' => 'c1', 'r2' => 'c2', 'r3' => null]);
    }

    public function testFlatten()
    {
        $args = \MUtil\Ra::args([0 => [0 => 'f', 1 => ['o' => '0', 0 => 'b']], 1 => ['a' => ['r' => 'r']]]);
        $this->assertEquals($args, [0 => 'f', 'o' => '0', 1 => 'b', 'a' => ['r' => 'r']]);
    }

    public function testFindKeysExists()
    {
        $data = [
            'row1' => ['c1' => 'a', 'c2' => 'd', 'c3' => 'g', 'c4' => 'j'],
            'row2' => ['c1' => 'b', 'c2' => 'e', 'c3' => 'h', 'c4' => 'k'],
            'row3' => ['c1' => 'c', 'c2' => 'f', 'c3' => 'i', 'c4' => 'l'],
        ];
        $keys = [
            'c1' => 'b',
            'c3' => 'h',
        ];
        $this->assertEquals(\MUtil\Ra::findKeys($data, $keys), 'row2');
    }

    public function testFindKeysExistsNot()
    {
        $data = [
            'row1' => ['c1' => 'a', 'c2' => 'd', 'c3' => 'g', 'c4' => 'j'],
            'row2' => ['c1' => 'b', 'c2' => 'e', 'c3' => 'h', 'c4' => 'k'],
            'row3' => ['c1' => 'c', 'c2' => 'f', 'c3' => 'i', 'c4' => 'l'],
        ];
        $keys = [
            'c1' => 'm',
            'c3' => 'o',
        ];
        $this->assertNull(\MUtil\Ra::findKeys($data, $keys));
    }

    public function testFindKeysExistsWrong()
    {
        $data = [
            'row1' => ['c1' => 'a', 'c2' => 'd', 'c3' => 'g', 'c4' => 'j'],
            'row2' => ['c1' => 'b', 'c2' => 'e', 'c3' => 'h', 'c4' => 'k'],
            'row3' => ['c1' => 'c', 'c2' => 'f', 'c3' => 'i', 'c4' => 'l'],
        ];
        $keys = [
            'c1' => 'b',
            'c3' => 'h',
        ];
        $this->assertNotEquals(\MUtil\Ra::findKeys($data, $keys), 'row3');
    }

    public function testKeySplit()
    {
        $args = [0 => '0', 'a' => 'a', 1 => '1', 'b' => 'b', '2' => '2'];
        list($nums, $strings) = \MUtil\Ra::keySplit($args);
        $this->assertEquals($nums, [0 => '0', 1 => '1', '2' => '2']);
        $this->assertEquals($strings, ['a' => 'a', 'b' => 'b']);
    }

    public function testKeySplitNumOnly()
    {
        $args = [0 => '0', 1 => '1', '2' => '2'];
        list($nums, $strings) = \MUtil\Ra::keySplit($args);
        $this->assertEquals($nums, [0 => '0', 1 => '1', '2' => '2']);
        $this->assertEquals($strings, []);
    }

    public function testKeySplitStringOnly()
    {
        $args = ['a' => 'a', 'b' => 'b'];
        list($nums, $strings) = \MUtil\Ra::keySplit($args);
        $this->assertEquals($nums, []);
        $this->assertEquals($strings, ['a' => 'a', 'b' => 'b']);
    }

    public function testKeySplitEmpty()
    {
        $args = [];
        list($nums, $strings) = \MUtil\Ra::keySplit($args);
        $this->assertEquals($nums, []);
        $this->assertEquals($strings, []);
    }

    public function testNonScalars1()
    {
        $a = new \stdClass();
        $a->b = 'c';
        $args = ['a', 'b', $a, 1];
        $result = \MUtil\Ra::nonScalars($args);
        $this->assertEquals($result, [$a]);
    }

    public function testNonScalars2()
    {
        $a = new \ArrayObject(['a', 'b', 1]);
        $args = array('a', 'b', $a, 1);
        $result = \MUtil\Ra::nonScalars($args);
        $this->assertEquals($result, [$a]);
    }

    public function testNonScalars3()
    {
        $a = new \ArrayObject(['a', 'b', 1]);
        $args = ['a', 'b', [$a], 1];
        $result = \MUtil\Ra::nonScalars($args);
        $this->assertEquals($result, [$a]);
    }

    public function testNonScalarsEmpty()
    {
        $args = [];
        $result = \MUtil\Ra::nonScalars($args);
        $this->assertEquals($result, []);
    }

    public function testNonScalarsNested()
    {
        $args = ['a', 'b', [0, 1]];
        $result = \MUtil\Ra::nonScalars($args);
        $this->assertEquals($result, []);
    }

    public function testNonScalarsNone()
    {
        $args = ['a', 'b', 1];
        $result = \MUtil\Ra::nonScalars($args);
        $this->assertEquals($result, []);
    }

    public function testNonScalarsNull()
    {
        $args = null;
        $result = \MUtil\Ra::nonScalars($args);
        $this->assertEquals($result, []);
    }

    public function testNonScalarsString()
    {
        $args = '';
        $result = \MUtil\Ra::nonScalars($args);
        $this->assertEquals($result, []);
    }
}
