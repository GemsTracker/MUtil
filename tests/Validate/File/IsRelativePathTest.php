<?php

namespace MUtilTest\Validate;

use MUtil\Validator\File\IsRelativePath;
use PHPUnit\Framework\TestCase;

class IsRelativePathTest extends TestCase
{
    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid(string $value)
    {
        $validator = new IsRelativePath();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid(?string $value, string $expectedMessageKey)
    {
        $validator = new IsRelativePath();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public function IsValidProvider()
    {
        return [
            ['test/hi/now'],
            ['test/hi/now'],
            ['../test/hi/now'],
        ];
    }

    public function IsInValidProvider()
    {
        return [
            ['/test', IsRelativePath::MATCH],
            ['\test', IsRelativePath::MATCH],
            ['C:\test', IsRelativePath::MATCH],
        ];
    }
}