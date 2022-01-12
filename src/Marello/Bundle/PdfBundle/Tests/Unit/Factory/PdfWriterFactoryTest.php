<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Factory;

use Marello\Bundle\PdfBundle\Factory\PdfWriterFactory;
use Mpdf\Mpdf;
use PHPUnit\Framework\TestCase;

class PdfWriterFactoryTest extends TestCase
{
    protected $factory;

    public function setUp(): void
    {
        $this->factory = new PdfWriterFactory();
    }

    public function testCreate()
    {
        $writer = $this->factory->create();

        $this->assertInstanceOf(Mpdf::class, $writer);
    }
}
