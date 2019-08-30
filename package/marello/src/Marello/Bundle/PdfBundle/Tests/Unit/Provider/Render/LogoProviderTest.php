<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Provider\Render;

use Marello\Bundle\InvoiceBundle\Entity\Invoice;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\PdfBundle\Provider\LogoProvider;
use Marello\Bundle\PdfBundle\Provider\Render\LogoProvider as LogoRenderProvider;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class LogoProviderTest extends TestCase
{
    use EntityTrait;

    protected $provider;

    public function setUp()
    {
        /** @var LogoProvider|\PHPUnit_Framework_MockObject_MockObject $logoProvider */
        $logoProvider = $this->createMock(LogoProvider::class);
        $this->provider = new LogoRenderProvider($logoProvider);
    }

    public function testSupportsInvoice()
    {
        $entity = new Invoice();

        $this->assertTrue($this->provider->supports($entity, []));
    }

    public function testSupportsOptions()
    {
        $entity = new Order();
        $options = [LogoRenderProvider::OPTION_KEY => new SalesChannel()];

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
        /** @var LogoProvider|\PHPUnit_Framework_MockObject_MockObject $logoProvider */
        $logoProvider = $this->createMock(LogoProvider::class);
        $logoProvider->expects($this->once())
            ->method('getInvoiceLogo')
            ->with($salesChannel, true)
            ->willReturn($logoPath)
        ;

        $provider = new LogoRenderProvider($logoProvider);

        $this->assertEquals(['logo' => $logoPath], $provider->getParams($entity, $options));
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
                'options' => [LogoRenderProvider::OPTION_KEY => $salesChannel2],
                'salesChannel' => $salesChannel2,
                'logoPath' => 'logo-from-options.jpg',
            ],
        ];
    }
}
