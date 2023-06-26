<?php

namespace Validate;

use MUtil\Validator\Base32;
use PHPUnit\Framework\TestCase;

class Base32Test extends TestCase
{
    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid(string $value)
    {
        $validator = new Base32();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid(?string $value, string $expectedMessageKey)
    {
        $validator = new Base32();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public function IsValidProvider()
    {
        return [
            ['ORSXG5A='],
            ['JRXXEZLNEBZWIZTTMRTHGZDG'],
        ];
    }

    public function IsInValidProvider()
    {
        return [
            ['', Base32::NOT_MATCH],
            [null, Base32::INVALID],
        ];
    }
}