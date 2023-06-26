<?php

namespace MUtilTest\Validate;

use MUtil\Validator\IsNot;
use PHPUnit\Framework\TestCase;

class IsNotTest extends TestCase
{
    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid(array|string|int $notAllowedValues, mixed $value)
    {
        $validator = new IsNot($notAllowedValues);
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid(array|string|int $notAllowedValues, mixed $value, string $expectedMessageKey)
    {
        $validator = new IsNot($notAllowedValues);
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public function IsValidProvider()
    {
        return [
            [[1,2,3,4], 5],
            [1, 2],
            ['a', 'b'],
            [['a', 'b', 'c', 'd'], 'e'],
        ];
    }

    public function IsInValidProvider()
    {
        return [
            [[1,2,3,4], 1, IsNot::NOT_ONE],
            [1, 1, IsNot::NOT_ONE],
            [[1,2,3,4], 3, IsNot::NOT_ONE],
            [['a', 'b', 'c', 'd'], 'b', IsNot::NOT_ONE],
            ['a', 'a', IsNot::NOT_ONE],
        ];
    }

}
