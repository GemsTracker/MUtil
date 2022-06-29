<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MUtilTest\Model\Iterator;

use PHPUnit\Framework\TestCase;

/**
 * Description of TextFileIteratorTest
 *
 * @author 175780
 */
class TextFileIteratorTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     *
     * @param string $filename
     * @param string $split
     * @param string $encoding
     * @return \MUtil_Model_Iterator_TextFileIterator
     */
    protected function getIterator(string $filename, string $split = ',', string $encoding = ''): \MUtil_Model_Iterator_TextFileIterator
    {
        $splitObject = new \MUtil_Model_Iterator_TextLineSplitter($split, $encoding);
        if ($encoding) {
            $splitFunc = array($splitObject, 'splitRecoded');
        } else {
            $splitFunc = array($splitObject, 'split');
        }

        $iterator = new \MUtil_Model_Iterator_TextFileIterator($filename, $splitFunc);

        return $iterator;
    }
    
    public function testCount()
    {
        $filename = str_replace('.php', '.txt', __FILE__);
        $iterator = $this->getIterator($filename);
        
        $count = $iterator->count();
        $this->assertEquals(3, $count);
    }

    public function testReadAllLines()
    {
        $filename = str_replace('.php', '.txt', __FILE__);
        $iterator = $this->getIterator($filename);
        foreach ($iterator as $line) {
            $actual[] = $line;
        }

        $expected = [
            [
                'line'  => 1,
                'to'    => 'a',
                'split' => 'b'
            ],
            [
                'line'  => 2,
                'to'    => 'c',
                'split' => 'd'
            ],
            [
                'line'  => 3,
                'to'    => 'e',
                'split' => 'f'
            ]
        ];

        $this->assertEquals($expected, $actual);
    }
    
    public function testSerialize()
    {
        $filename = __CLASS__ . '.txt';
        $iterator = $this->getIterator($filename);
        $iterator->next();  //We are at the second record now
        $expected = $iterator->current();
        
        $frozen = serialize($iterator);
        $newIterator = unserialize($frozen);
        
        $actual = $newIterator->current();
        $this->assertEquals($expected, $actual);
    }

}
