<?php

namespace MUtilTest\Validate;

use MUtil\Validate\IsConfirmed;
use PHPUnit\Framework\TestCase;

class IsConfirmedTest extends TestCase
{
    public function testMissingField()
    {
        $validator = new IsConfirmed();
        $result = $validator->isValid('testValue');
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey(IsConfirmed::MISSING_FIELD_NAME, $messages);
    }

    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid($value, array $context)
    {
        $validator = new IsConfirmed('testField1', 'Test field 1');
        $result = $validator->isValid($value, $context);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid($value, array $context, string $expectedMessageKey)
    {
        $validator = new IsConfirmed('testField1', 'Test field 1');
        $result = $validator->isValid($value, $context);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public function IsValidProvider()
    {
        return [
            ['abcdefg', ['testField1' => 'abcdefg']],
            [null, ['testField1' => null]],
            [123456, ['testField1' => 123456]],
        ];
    }

    public function IsInValidProvider()
    {
        return [
            ['abcdefg', ['testField1' => 'gddgfhjlgfh'], IsConfirmed::NOT_SAME],
            ['abcdefg', ['testField1' => null], IsConfirmed::NOT_SAME],
            [123456, ['testField1' => 456789], IsConfirmed::NOT_SAME],
            [null, ['testField1' => false], IsConfirmed::NOT_SAME],
            ['abcdefg', [], IsConfirmed::MISSING_DATA],
            ['abcdefg', ['testField2' => 'abcdefg'], IsConfirmed::MISSING_DATA],
        ];
    }

}
