<?php

namespace MUtilTest\Validate;

use MUtil\Validator\ElevenTest;
use PHPUnit\Framework\TestCase;

class ElevenTestTest extends TestCase
{
    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid($value)
    {
        $validator = new ElevenTest();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }

    public function testSetNumberOrder()
    {
        $validator = new ElevenTest();
        $validator->setNumberOrder(ElevenTest::ORDER_RIGHT_2_LEFT);
        $result = $validator->isValid(418);
        $this->assertTrue($result);

        $validator->setNumberOrder(ElevenTest::ORDER_LEFT_2_RIGHT);
        $result = $validator->isValid(418);
        $this->assertFalse($result);

        $result = $validator->isValid(814);
        $this->assertTrue($result);

        $validator->setNumberOrder([2,1,3]);
        $result = $validator->isValid(184);
        $this->assertTrue($result);
    }

    public function testSetNumberLength()
    {
        $validator = new ElevenTest();
        $validator->setNumberLength(3);

        $result = $validator->isValid(814);
        $this->assertTrue($result);

        $result = $validator->isValid(15);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey(ElevenTest::TOO_SHORT, $messages);

        $result = $validator->isValid(7143);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey(ElevenTest::TOO_LONG, $messages);
    }

    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid($value, string $expectedMessageKey)
    {
        $validator = new ElevenTest();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($expectedMessageKey, $messages);
    }

    public function IsValidProvider()
    {
        return [
            [15],
            ['15'],
            [814],
            [7143],
        ];
    }

    public function IsInValidProvider()
    {
        return [
            [1, ElevenTest::NOT_CHECK],
            [null, ElevenTest::NOT_NUMBER],
            ['abc', ElevenTest::NOT_NUMBER],
        ];
    }

}
