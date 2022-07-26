<?php

namespace MUtilTest;

use PHPUnit\Framework\TestCase;

/**
 * Unit test for class MUtil\Html
 *
 * @author     Michiel Rook <info@touchdownconsulting.nl>
 * @package    MUtil
 * @subpackage Html
 */
class HtmlTest extends TestCase
{
    public function testValidCreator()
    {
        $creator = \MUtil\Html::getCreator();
        
        $this->assertInstanceOf('\\MUtil\\Html\\Creator', $creator);
    }
    
    public function testValidRenderer()
    {
        $renderer = \MUtil\Html::getRenderer();
        
        $this->assertInstanceOf('\\MUtil\\Html\\Renderer', $renderer);
    }
    
    public function testDiv()
    {
        $div = \MUtil\Html::div('bar', ['id' => 'foo']);
        
        $this->assertInstanceOf('\\MUtil\\Html\\HtmlElement', $div);
        $this->assertEquals('div', $div->getTagName());
        $this->assertEquals('foo', $div->getAttrib('id'));
    }
}
