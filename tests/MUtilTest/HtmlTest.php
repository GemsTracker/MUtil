<?php

namespace MUtilTest;

use PHPUnit\Framework\TestCase;

/**
 * Unit test for class MUtil_Html
 *
 * @author     Michiel Rook <info@touchdownconsulting.nl>
 * @package    MUtil
 * @subpackage Html
 */
class HtmlTest extends TestCase
{
    public function testValidCreator()
    {
        $creator = \MUtil_Html::getCreator();
        
        $this->assertInstanceOf('MUtil_Html_Creator', $creator);
    }
    
    public function testValidRenderer()
    {
        $renderer = \MUtil_Html::getRenderer();
        
        $this->assertInstanceOf('MUtil_Html_Renderer', $renderer);
    }
    
    public function testDiv()
    {
        $div = \MUtil_Html::div('bar', ['id' => 'foo']);
        
        $this->assertInstanceOf('MUtil_Html_HtmlElement', $div);
        $this->assertEquals('div', $div->getTagName());
        $this->assertEquals('foo', $div->getAttrib('id'));
    }
}
