<?php

namespace Marello\Bundle\PdfBundle\Renderer;

use Marello\Bundle\PdfBundle\Factory\PdfWriterFactory;
use Mpdf\Output\Destination;

class HtmlRenderer
{
    protected $pdfWriterFactory;

    public function __construct(PdfWriterFactory $pdfWriterFactory)
    {
        $this->pdfWriterFactory = $pdfWriterFactory;
    }

    public function render($input)
    {
        $writer = $this->getWriter();

        $writer->WriteHTML($input);

        return $writer->Output(null, Destination::STRING_RETURN);
    }

    public function renderToFile($input, $filename)
    {
        $writer = $this->getWriter();

        $writer->WriteHTML($input);

        return $writer->Output($filename, Destination::FILE);
    }

    protected function getWriter()
    {
        return $this->pdfWriterFactory->create();
    }
}
