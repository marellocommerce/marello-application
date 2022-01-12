<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Twig\Extension;

use Marello\Bundle\PdfBundle\Provider\DocumentTableProvider;
use Marello\Bundle\PdfBundle\Twig\Extension\DocumentTableExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

class DocumentTableExtensionTest extends TestCase
{
    protected $extension;

    public function setUp(): void
    {
        /** @var DocumentTableProvider|\PHPUnit\Framework\MockObject\MockObject $tableProvider */
        $tableProvider = $this->createMock(DocumentTableProvider::class);

        $this->extension = new DocumentTableExtension($tableProvider);
    }

    public function testGetFilters()
    {
        $this->assertCount(0, $this->extension->getFilters());
    }

    public function testGetNodeVisitors()
    {
        $this->assertCount(0, $this->extension->getNodeVisitors());
    }

    public function testGetOperators()
    {
        $this->assertCount(0, $this->extension->getOperators());
    }

    public function testGetTests()
    {
        $this->assertCount(0, $this->extension->getTests());
    }

    public function testGetTokenParsers()
    {
        $this->assertCount(0, $this->extension->getTokenParsers());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();

        $this->assertCount(1, $functions);

        /** @var TwigFunction $twigFunction */
        $twigFunction = reset($functions);
        $this->assertEquals('get_document_tables', $twigFunction->getName());
        $this->assertTrue(is_callable($twigFunction->getCallable()));
    }

    public function testGetName()
    {
        $this->assertEquals(DocumentTableExtension::NAME, $this->extension->getName());
    }
}
