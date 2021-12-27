<?php

namespace Marello\Bundle\InvoiceBundle\Tests\Unit\Pdf\Logo;

use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\InvoiceBundle\Pdf\Logo\InvoiceLogoPathProvider;
use Marello\Bundle\InvoiceBundle\Pdf\Logo\InvoiceLogoRenderParameterProvider;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class InvoiceLogoRenderParameterProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @var InvoiceLogoRenderParameterProvider
     */
    private $provider;

    /**
     * @var InvoiceLogoPathProvider
     */
    private $logoPathProvider;

    public function setUp(): void
    {
        $this->logoPathProvider = $this->createMock(InvoiceLogoPathProvider::class);
        $this->provider = new InvoiceLogoRenderParameterProvider($this->logoPathProvider);
    }

    public function testSupportsInvoice()
    {
        $entity = new Invoice();

        $this->assertTrue($this->provider->supports($entity, []));
    }

    public function testSupportsOptions()
    {
        $entity = new Order();
        $options = [InvoiceLogoRenderParameterProvider::OPTION_KEY => new SalesChannel()];

        $this->assertTrue($this->provider->supports($entity, $options));
    }

    public function testSupportsUnsupported()
    {
        $entity = new Order();
        $options = [];

        $this->assertFalse($this->provider->supports($entity, $options));
    }

    /**
     * @param $entity
     * @param $options
     * @param $salesChannel
     * @param $logoPath
     *
     * @dataProvider getParamsProvider
     */
    public function testGetParams($entity, $options, $salesChannel, $logoPath)
    {
        $this->logoPathProvider->expects($this->once())
            ->method('getInvoiceLogo')
            ->with($salesChannel, true)
            ->willReturn($logoPath)
        ;

        $this->assertEquals(['logo' => $logoPath, 'logo_width' => null], $this->provider->getParams($entity, $options));
    }

    public function getParamsProvider()
    {
        /** @var SalesChannel $salesChannel1 */
        $salesChannel1 = $this->getEntity(SalesChannel::class, [
            'id' => 1,
            'name' => 'Sales Channel 1',
            'code' => 'channel-1',
        ]);
        /** @var SalesChannel $salesChannel2 */
        $salesChannel2 = $this->getEntity(SalesChannel::class, [
            'id' => 2,
            'name' => 'Sales Channel 2',
            'code' => 'channel-2',
        ]);

        $invoice = new Invoice();
        $invoice->setSalesChannel($salesChannel1);

        return [
            'from entity' => [
                'entity' => $invoice,
                'options' => [],
                'salesChannel' => $salesChannel1,
                'logoPath' => 'logo-from-entity.jpg',
            ],
            'from options' => [
                'entity' => new Order(),
                'options' => [InvoiceLogoRenderParameterProvider::OPTION_KEY => $salesChannel2],
                'salesChannel' => $salesChannel2,
                'logoPath' => 'logo-from-options.jpg',
            ],
        ];
    }
}
