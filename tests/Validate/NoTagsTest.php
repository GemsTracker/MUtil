<?php

namespace MUtilTest\Validate;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use MUtil\Validator\NoTags;
use PHPUnit\Framework\TestCase;

/**
 * Description of NoTags
 *
 * @author 175780
 */
class NoTagsTest extends TestCase
{

    /**
     * @dataProvider IsValidProvider
     */
    public function testIsValid($value)
    {
        $validator = new NoTags();
        $result = $validator->isValid($value);
        $this->assertTrue($result);
    }
    
    /**
     * @dataProvider IsInValidProvider
     */
    public function testIsInValid($value, $messageKey)
    {
        $validator = new NoTags();
        $result = $validator->isValid($value);
        $this->assertFalse($result);
        $messages = $validator->getMessages();
        $this->assertArrayHasKey($messageKey, $messages);
    }

    public function IsValidProvider()
    {
        return [
            'valid#1' => [ "< allowed" ],
            'valid#2' => [ "<1" ],
            'valid#3' => [ "tom&jerry@wb.com" ]
        ];
    }
    
    public function IsInValidProvider()
    {
        return [
            'invalid#1' => ['<abc', NoTags::MATCH],
            'invalid#2' => ['<ABC', NoTags::MATCH],
            'invalid#3' => ['</abc', NoTags::MATCH],
            'invalid#4' => ['<\abc', NoTags::MATCH],
            'invalid#5' => ['<:abc', NoTags::MATCH],
            'invalid#6' => ['&nbsp;', NoTags::MATCH],
            'invalid#7' => ['&#160;', NoTags::MATCH],
            'invalid#8' => ['&#x000A0;', NoTags::MATCH],
            'invalid#9' => ['&#X000A0;', NoTags::MATCH],
        ];
    }

}
