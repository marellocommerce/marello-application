<?php

namespace Marello\Bundle\PdfBundle\Tests\Unit\Provider\Logo;

use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\PdfBundle\Provider\LogoPathProvider;
use Marello\Bundle\PdfBundle\Tests\Unit\Mock\SalesChannelAwareModel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;
use Marello\Bundle\PdfBundle\Provider\Render\LogoRenderParameterProvider;

class LogoRenderParameterProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @var LogoRenderParameterProvider
     */
    private $provider;

    /**
     * @var LogoPathProvider
     */
    private $logoPathProvider;

    public function setUp(): void
    {
        $this->logoPathProvider = $this->createMock(LogoPathProvider::class);
        $this->provider = new LogoRenderParameterProvider($this->logoPathProvider);
    }

    public function testSupportsSalesChannelAware()
    {
        $entity = new SalesChannelAwareModel();

        $this->assertTrue($this->provider->supports($entity, []));
    }

    public function testSupportsOptions()
    {
        $entity = new Organization();
        $options = [LogoRenderParameterProvider::OPTION_KEY => new SalesChannel()];

        $this->assertTrue($this->provider->supports($entity, $options));
    }

    public function testSupportsUnsupported()
    {
        $entity = new Organization();
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
            ->method('getLogo')
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

        $entity = new SalesChannelAwareModel();
        $entity->setSalesChannel($salesChannel1);

        return [
            'from entity' => [
                'entity' => $entity,
                'options' => [],
                'salesChannel' => $salesChannel1,
                'logoPath' => 'logo-from-entity.jpg',
            ],
            'from options' => [
                'entity' => new Organization(),
                'options' => [LogoRenderParameterProvider::OPTION_KEY => $salesChannel2],
                'salesChannel' => $salesChannel2,
                'logoPath' => 'logo-from-options.jpg',
            ],
        ];
    }
}
