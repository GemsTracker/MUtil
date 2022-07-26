<?php

namespace MUtilTest\Model;

use PHPUnit\Framework\TestCase;

class FormBridgeTest extends TestCase
{
    /**
     * The options array as set in the setUp()
     *
     * @var array
     */
    protected $options;

    /**
     *
     * @var \MUtil\Model\ModelAbstract
     */
    protected $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = [
            'date' => [
                'dateFormat' => 'dd-MM-yyyy',
                'description' => 'dd-mm-yyyy',
                'size'        => 10,
                'jQueryParams' => [
                    'buttonImage' => 'datepicker.png',
                    'changeMonth' => true,
                    'changeYear' => true,
                    'duration' => 'fast',
                    'showOn' => 'button'
                ],
            ],
        ];
        
        \MUtil\Model\Bridge\FormBridge::setFixedOptions($this->options);
        
        parent::setUpBeforeClass();
    }

    public function tearDown(): void
    {

    }

    public function testOptions()
    {
        $model = $this->model;
        $form  = new \Zend_Form();

        //Unchanged when not in the fixedOptions
        $options = ['description'=>'dd-mm-yyyy'];
        $original = $options;
        \MUtil\Model\Bridge\FormBridge::applyFixedOptions('input', $options);
        $this->assertEquals($original, $options);

        //Overruled and extended when in the fixedOptions
        $options = $original;
        \MUtil\Model\Bridge\FormBridge::applyFixedOptions('date', $options);
        $expected = $this->options['date'];
        $this->assertEquals($expected, $options);
    }
}