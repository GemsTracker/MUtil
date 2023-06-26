<?php

namespace Validate;

use MUtil\Validator\Phone;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid(string $value)
    {
        $validator = new Phone();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid(?string $value, string $expectedMessageKey)
    {
        $validator = new Phone();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public function IsValidProvider()
    {
        return [
            ['0612345678'],
            ['0401234567'],
            ['+31 401234567'],
            ['+31 (0) 401234567'],
        ];
    }

    public function IsInValidProvider()
    {
        return [
            ['abcde', Phone::NOT_MATCH],
            ['%^$#4568789', Phone::NOT_MATCH],
            [null, Phone::INVALID],
        ];
    }
}