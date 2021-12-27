<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Renderer;

use Marello\Bundle\PdfBundle\Factory\PdfWriterFactory;
use Marello\Bundle\PdfBundle\Renderer\HtmlRenderer;
use Mpdf\Mpdf;
use PHPUnit\Framework\TestCase;

class HtmlRendererTest extends TestCase
{
    /** @var HtmlRenderer */
    protected $renderer;

    public function setUp(): void
    {
        /** @var PdfWriterFactory|\PHPUnit\Framework\MockObject\MockObject */
        $factory = $this->createMock(PdfWriterFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->willReturn(new Mpdf())
        ;

        $this->renderer = new HtmlRenderer($factory);
    }

    public function tearDown(): void
    {
        $files = ['/tmp/pdf_single.pdf', '/tmp/pdf_multiple.pdf'];

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * @param $input
     * @param $expected
     *
     * @dataProvider renderProvider
     */
    public function testRender($input, $expected)
    {
        $generatedPdf = $this->renderer->render($input);
        $expectedMagick = new \Imagick();
        $expectedMagick->readImageBlob($expected);
        $expectedMagick->resetIterator();
        $expectedMagick = $expectedMagick->appendImages(true);

        $generatedImagick = new \Imagick();
        $generatedImagick->readImageBlob($generatedPdf);
        $generatedImagick->resetIterator();
        $generatedImagick = $generatedImagick->appendImages(true);

        $diff = $expectedMagick->compareImages($generatedImagick, 1);

        $this->assertSame(0.0, $diff[1]);
    }

    /**
     * @param $input
     * @param $expected
     *
     * @dataProvider renderProvider
     */
    public function testRenderToFile($input, $expected, $filename)
    {
        $generatedPdf = $this->renderer->renderToFile($input, $filename);

        $expectedMagick = new \Imagick();
        $expectedMagick->readImageBlob($expected);
        $expectedMagick->resetIterator();
        $expectedMagick = $expectedMagick->appendImages(true);

        $generatedImagick = new \Imagick();
        $generatedImagick->readImageFile(fopen($filename, 'r'));
        $generatedImagick->resetIterator();
        $generatedImagick = $generatedImagick->appendImages(true);

        $diff = $expectedMagick->compareImages($generatedImagick, 1);

        $this->assertSame(0.0, $diff[1]);
    }

    /**
     * @return array
     */
    public function renderProvider()
    {
        return [
            'single page' => [
                'input' => '<html><head><title>PDF test</title></head><body><h1>Page 1</h1><p>Page 1</p></body></html>',
                'expected' => base64_decode(file_get_contents(__DIR__.'/data/single.txt')),
                'filename' => '/tmp/pdf_single.pdf',
            ],
            'multiple pages' => [
                'input' => '<html><head><title>PDF test</title></head><body><h1>Page 1</h1><p>Page 1</p><pagebreak><h2>Page 2</h2><p>Page 2</p></body></html>',
                'expected' => base64_decode(file_get_contents(__DIR__.'/data/multiple.txt')),
                'filename' => '/tmp/pdf_multiple.pdf',
            ],
        ];
    }
}
