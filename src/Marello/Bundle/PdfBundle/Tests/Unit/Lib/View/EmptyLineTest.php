<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Lib\View;

use Marello\Bundle\PdfBundle\Lib\View\EmptyDisplayLine;
use Marello\Bundle\PdfBundle\Lib\View\EmptyLine;
use PHPUnit\Framework\TestCase;

class EmptyLineTest extends TestCase
{
    protected $line;

    public function setUp(): void
    {
        $this->line = new EmptyLine();
    }

    public function testOffsetSet()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->line['not set value'] = 'set value';
    }

    public function testOffsetGet()
    {
        $value = $this->line['not set value'];

        $this->assertTrue(is_array($value));
        $this->assertCount(1, $value);
        $this->assertNull(reset($value));
    }

    public function testOffsetUnset()
    {
        $this->expectException(\BadMethodCallException::class);

        unset($this->line['not set value']);
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->line['not set value']));
    }

    public function testGetDisplayLines()
    {
        $displayLines = $this->line->getDisplayLines();

        $this->assertTrue(is_array($displayLines));
        $this->assertCount(1, $displayLines);
        foreach ($displayLines as $line) {
            $this->assertInstanceOf(EmptyDisplayLine::class, $line);
        }
    }

    public function testGetHeight()
    {
        $this->assertEquals(1, $this->line->getHeight());
    }

    public function testIsEmpty()
    {
        $this->assertTrue($this->line->isEmpty());
    }
}
