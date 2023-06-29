<?php

namespace MUtilTest\Validate;

use MUtil\Validator\Dutch\BankAccount;
use MUtil\Validator\ElevenTest;
use PHPUnit\Framework\TestCase;

class BankAccountTest extends TestCase
{
    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid($value)
    {
        $validator = new BankAccount();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid($value, string $expectedMessageKey)
    {
        $validator = new BankAccount();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public static function IsValidProvider()
    {
        return [
            [280814526],
            ['416961347'],
            ['790622033'],
        ];
    }

    public static function IsInValidProvider()
    {
        return [
            [1, BankAccount::TOO_SHORT],
            [1234567890, BankAccount::TOO_LONG],
            [123459879, BankAccount::NOT_CHECK],
        ];
    }

}
