<?php

namespace MUtilTest\Model;

use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->model = $this->getMockForAbstractClass('\\MUtil\\Model\\ModelAbstract', ['testAbstractModel']);
    }
       
    /**
     * @dataProvider providerAddFilterDataProvider
     */
    public function testAddFilter($initial, $extra, $expected)
    {
        $model = $this->model;
        $model->setFilter($initial);
        $model->addFilter($extra);
        $this->assertEquals($expected, $model->getFilter());
    }
    
    /**
     * 
     * @return array
     */
    public function providerAddFilterDataProvider()
    {
        return array(
            [  // Simple first test
                [
                    'testfield' => 'stringvalue'
                ],
                [
                    'field2'    => 2
                ],
                [
                    'testfield' => 'stringvalue',
                    'field2'    => 2
                ],
            ],
            [  // Check remove duplicates
                [
                    'testfield' => 'stringvalue'
                ],
                [
                    'stringvalue'
                ],
                [
                    'testfield' => 'stringvalue'
                ],
            ],
            [  // Check remove duplicates
                [
                    'testfield' => 'stringvalue'
                ],
                [
                    'testfield' => 'stringvalue'
                ],
                [
                    'testfield' => 'stringvalue'
                ],
            ],
            [  // Check mixed types
                [
                    'testfield' => 1
                ],
                [
                    'stringvalue'
                ],
                [
                    'testfield' => 1,
                    0           => 'stringvalue'
                ],
            ],
            [  // Check mixed types bug #838
                [
                    'testfield' => 0,
                ],
                [
                    'stringvalue',
                    'test'
                ],
                [
                    'testfield' => 0,
                    0           => 'stringvalue',
                    1           => 'test'
                ],
            ],
        );
    }
}
