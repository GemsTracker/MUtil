<?php

namespace MUtilTest\Validate;

use MUtil\Validator\ExistingUrl;
use PHPUnit\Framework\TestCase;

class ExistingUrlTest extends TestCase
{
    public function testDisabledCurlCheck()
    {
        $validator = new ExistingUrl();
        $validator::$disabled = true;
        $result = $validator->isValid('https://thisurlwillnotbecheckedbycurl.dev');
        $this->assertTrue($result);
        $validator::$disabled = false;
    }

    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid(string $value)
    {
        $validator = new ExistingUrl();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid(?string $value, string $expectedMessageKey)
    {
        $validator = new ExistingUrl();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public static function IsValidProvider()
    {
        return [
            ['http://www.wikipedia.nl'],
            ['https://www.wikipedia.nl'],
            ['https://wikipedia.nl'],
            ['https://8.8.8.8'],
            ['https://docs.mezzio.dev'],
        ];
    }

    public static function IsInValidProvider()
    {
        return [
            ['test@wikipedia.nl', ExistingUrl::ERROR_URL_NOT_VALID],
            // These tests should fail in the curl check, but will delay test suits by the curl timeout, so have been disabled
            // ['https://somethingnonexisting', ExistingUrl::ERROR_SITE_NOT_FOUND],
            // ['https://test.local', \MUtil_Validate_ExistingUrl::ERROR_SITE_NOT_FOUND],
        ];
    }
}