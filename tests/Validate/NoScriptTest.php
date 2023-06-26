<?php

namespace MUtilTest\Validate;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use MUtil\Validator\NoScript;
use PHPUnit\Framework\TestCase;

/**
 * Description of NoTags
 *
 * @author 175780
 */
class NoScriptTest extends TestCase
{

    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid($value)
    {
        $validator = new NoScript();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }
    
    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid($value, $messageKey)
    {
        $validator = new NoScript();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($messageKey, $messages);
    }

    public function IsValidProvider()
    {
        return [
            'valid#1' => ['allowed'],
            'valid#2' => ['1'],
            'valid#3' => ['tom&jerry@wb.com'],
        ];
    }
    
    public function IsInValidProvider()
    {
        return [
            'invalid#1' => ['<abc', NoScript::MATCH],
            'invalid#2' => ['<ABC', NoScript::MATCH],
            'invalid#3' => ['</abc', NoScript::MATCH],
            'invalid#4' => ['<\abc', NoScript::MATCH],
            'invalid#5' => ['<:abc', NoScript::MATCH],
            'invalid#6' => [null, NoScript::INVALID],

        ];
    }

}
