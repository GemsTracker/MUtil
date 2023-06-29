<?php

namespace MUtilTest\Validate;

use MUtil\Validator\Dutch\BankAccount;
use MUtil\Validator\Dutch\Burgerservicenummer;
use MUtil\Validator\ElevenTest;
use PHPUnit\Framework\TestCase;

class BurgerservicenummerTest extends TestCase
{
    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid($value)
    {
        $validator = new Burgerservicenummer();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid($value, string $expectedMessageKey)
    {
        $validator = new Burgerservicenummer();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public static function IsValidProvider()
    {
        return [
            ['000000012'],
            ['999999989'],
            [999999989],
            ['999990883'],
        ];
    }

    public static function IsInValidProvider()
    {
        return [
            [1, Burgerservicenummer::TOO_SHORT],
            [1234567890, Burgerservicenummer::TOO_LONG],
            ['000000013', Burgerservicenummer::NOT_CHECK],
        ];
    }

}
