<?php

namespace MUtilTest\Validate;

use MUtil\Validator\RequireOtherField;
use PHPUnit\Framework\TestCase;

class RequireOtherFieldTest extends TestCase
{
    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid(mixed $value, ?array $context)
    {
        $validator = new RequireOtherField('Test field2', 'testfield1', 'Test field 1');
        $result = $validator->isValid($value, $context);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid(mixed $value, ?array $context, string $expectedMessageKey)
    {
        $validator = new RequireOtherField('Test field2', 'testfield1', 'Test field 1');
        $result = $validator->isValid($value, $context);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public function IsValidProvider()
    {
        return [
            [1, ['testfield1' => 'test123']],
            [null, ['testfield1' => 'test123']],
        ];
    }

    public function IsInValidProvider()
    {
        return [
            //[1, null, RequireOtherField::REQUIRED],
            [2, [],RequireOtherField::REQUIRED],
            [3, ['testfield2' => 'x'],RequireOtherField::REQUIRED],
            [4, ['testfield1' => null],RequireOtherField::REQUIRED],
            [5, ['testfield1' => false],RequireOtherField::REQUIRED],
        ];
    }

}
