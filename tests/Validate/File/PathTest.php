<?php

namespace MUtilTest\Validate;

use MUtil\Validator\Base32;
use MUtil\Validator\File\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid(string $value)
    {
        $validator = new Path();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid(?string $value, string $expectedMessageKey)
    {
        $validator = new Path();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public static function IsValidProvider()
    {
        return [
            ['/test/hi/now'],
            ['C:\test\hi'],
            ['test/hi/now'],
            ['../test/hi/now'],
        ];
    }

    public static function IsInValidProvider()
    {
        return [
            [':/test', Path::MATCH],
            ['?/test', Path::MATCH],
            ['*/test', Path::MATCH],
            ['|/test', Path::MATCH],
            ['"/test', Path::MATCH],
            ['</test', Path::MATCH],
            ['>/test', Path::MATCH],
            ['>/test', Path::MATCH],
        ];
    }
}