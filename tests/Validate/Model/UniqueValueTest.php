<?php

namespace Validate\Model;

use MUtil\Model\PlaceholderModel;
use MUtil\Validate\Model\UniqueValue;
use PHPUnit\Framework\TestCase;

class UniqueValueTest extends TestCase
{
    protected function getModel()
    {
        $model = new PlaceholderModel('test',
            [
                'field1',
                'field2'
            ],
            [
                [
                    'field1' => 'abc',
                    'field2' => 'def',
                ],
            ],
        );
        $model->setKeys(['field1']);

        return $model;
    }

    public function testIsValid()
    {
        $validator = new UniqueValue($this->getModel(), 'field1');
        $result = $validator->isValid('123');
        $this->assertTrue($result);
    }

    public function testIsInValid()
    {
        $validator = new UniqueValue($this->getModel(), 'field1');
        $result = $validator->isValid('abc');
        $this->assertTrue($result);
    }
}