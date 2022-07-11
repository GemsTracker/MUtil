<?php

namespace MUtilTest\Validate;

use MUtil\Validate\NoCsvInjectionChars;
use PHPUnit\Framework\TestCase;

class NoCsvInjectionCharsTest extends TestCase
{
    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid(string $value)
    {
        $validator = new NoCsvInjectionChars();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid(?string $value, string $expectedMessageKey)
    {
        $validator = new NoCsvInjectionChars();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public function IsValidProvider()
    {
        return [
            ['abcdefg'],
            ['abc efg'],
            ['abc 123'],
            ['abc&@'],
        ];
    }

    public function IsInValidProvider()
    {
        return [
            ['abcd=', NoCsvInjectionChars::MATCH],
            ['abcd|bcv', NoCsvInjectionChars::MATCH],
            ['abcd+', NoCsvInjectionChars::MATCH],
            [null, NoCsvInjectionChars::INVALID],
        ];
    }

}
