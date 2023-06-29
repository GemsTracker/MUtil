<?php

namespace MUtilTest\Validate;

use MUtil\Validator\IsNot;
use MUtil\Validator\SimpleEmail;
use PHPUnit\Framework\TestCase;

class SimpleEmailTest extends TestCase
{
    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid($value)
    {
        $validator = new SimpleEmail();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid($value, string $expectedMessageKey)
    {
        $validator = new SimpleEmail();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public static function IsValidProvider()
    {
        return [
            ['example@email.com'],
            ['example.first.middle.lastname@email.com'],
            ['example@subdomain.email.com'],
            ['example+firstname+lastname@email.com'],
            //['example@234.234.234.234'],
            //['example@[234.234.234.234]'],
            //['“example”@email.com'],
            ['0987654321@example.com'],
            ['example@email-one.com'],
            ['_______@email.com'],
            ['example@email.name'],
            ['example@email.museum'],
            ['example@email.co.jp'],
            ['example.firstname-lastname@email.com'],
        ];
    }

    public static function IsInValidProvider()
    {
        return [
            ['plaintextaddress', SimpleEmail::NOT_MATCH],
            ['@#@@##@%^%#$@#$@#.com', SimpleEmail::NOT_MATCH],
            ['@email.com', SimpleEmail::NOT_MATCH],
            ['John Doe <example@email.com>', SimpleEmail::NOT_MATCH],
            ['example.email.com', SimpleEmail::NOT_MATCH],
            ['example@example@email.com', SimpleEmail::NOT_MATCH],
            ['.example@email.com', SimpleEmail::NOT_MATCH],
            ['example.@email.com', SimpleEmail::NOT_MATCH],
            ['example…example@email.com', SimpleEmail::NOT_MATCH],
            ['おえあいう@example.com', SimpleEmail::NOT_MATCH],
            ['example@email.com (John Doe)', SimpleEmail::NOT_MATCH],
            ['example@email', SimpleEmail::NOT_MATCH],
            ['example@-email.com', SimpleEmail::NOT_MATCH],
            ['example@111.222.333.44444', SimpleEmail::NOT_MATCH],
            ['example@email…com', SimpleEmail::NOT_MATCH],
            ['CAT…123@email.com', SimpleEmail::NOT_MATCH],
            ['”(),:;<>[\]@email.com', SimpleEmail::NOT_MATCH],
            ['obviously”not”correct@email.com', SimpleEmail::NOT_MATCH],
            ['example\ is”especially”not\allowed@email.com', SimpleEmail::NOT_MATCH],
        ];
    }

}
