<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Lib\View;

use Marello\Bundle\PdfBundle\Lib\View\EmptyDisplayLine;
use PHPUnit\Framework\TestCase;

class EmptyDisplayLineTest extends TestCase
{
    protected $line;

    public function setUp(): void
    {
        $this->line = new EmptyDisplayLine();
    }

    public function testOffsetGet()
    {
        $this->assertNull($this->line['not set value']);
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->line['not set value']));
    }

    public function testOffsetSet()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->line['not set value'] = 'set value';
    }

    public function testOffsetUnset()
    {
        $this->expectException(\BadMethodCallException::class);

        unset($this->line['set value']);
    }
}
