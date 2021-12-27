<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\DependencyInjection;

use Marello\Bundle\PdfBundle\DependencyInjection\MarelloPdfExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class MarelloPdfExtensionTest extends ExtensionTestCase
{
    /**
     * @var MarelloPdfExtension
     */
    protected $extension;

    protected function setUp(): void
    {
        $this->extension = new MarelloPdfExtension();
    }

    protected function tearDown(): void
    {
        unset($this->extension);
    }

    public function testLoad()
    {
        $this->loadExtension($this->extension);

        $this->assertDefinitionsLoaded([
            'marello_pdf.factory.pdf_writer',
            'marello_pdf.renderer.html',
            'marello_pdf.renderer.twig',
            'marello_pdf.provider.render_parameters',
            'marello_pdf.param_provider.entity',
            'marello_pdf.param_provider.saleschannel_config_values',
            'marello_pdf.provider.document_table',
            'marello_pdf.twig_extension.document_table',
        ]);
    }
}
