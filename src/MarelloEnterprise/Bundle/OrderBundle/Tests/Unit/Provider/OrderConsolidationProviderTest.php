<?php

namespace MarelloEnterprise\Bundle\OrderBundle\Tests\Unit\Provider;

use MarelloEnterprise\Bundle\OrderBundle\Provider\OrderConsolidationProvider;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

use PHPUnit\Framework\TestCase;

use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use MarelloEnterprise\Bundle\OrderBundle\Form\Extension\OrderExtension;

class OrderConsolidationProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @var FormInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $form;

    /**
     * @var FormEvent|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $formEvent;

    /**
     * @var Order|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entity;

    /**
     * @var SalesChannel|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $salesChannel;

    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configManager;

    /**
     * @var OrderConsolidationProvider
     */
    protected $orderConsolidationProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->entity = $this->createMock(Order::class);
        $this->salesChannel = $this->createMock(SalesChannel::class);
        $this->entity
            ->expects(static::any())
            ->method('getSalesChannel')
            ->willReturn($this->salesChannel);

        $this->configManager = $this->createMock(ConfigManager::class);
        $this->orderConsolidationProvider = new OrderConsolidationProvider($this->configManager);
    }

    /**
     * Test consolidation feature is not enabled
     * @return void
     */
    public function testIsConsolidationFeatureEnabled(): void
    {
        $this->configManager
            ->expects(static::once())
            ->method('get')
            ->with(
                'marello_enterprise_order.enable_order_consolidation'
            )
            ->willReturn(true);
        self::assertTrue($this->orderConsolidationProvider->isConsolidationFeatureEnabled());
    }

    /**
     * Test consolidation not enabled on saleschannel scope
     * @return void
     */
    public function testIsConsolidationEnabledForSalesChannel(): void
    {
        $this->configManager
            ->expects(static::once())
            ->method('get')
            ->with(
                'marello_enterprise_order.set_consolidation_for_scope',
                false,
                false,
                $this->salesChannel
            )
            ->willReturn(true);

        self::assertTrue($this->orderConsolidationProvider->isConsolidationEnabledForSalesChannel($this->entity));
    }

    /**
     * Test consolidation not enabled in system settings scope
     * @return void
     */
    public function testIsConsolidationEnabledInSystem(): void
    {
        $this->configManager
            ->expects(static::once())
            ->method('get')
            ->with(
                'marello_enterprise_order.set_consolidation_for_scope',
                false,
                false,
                null
            )
            ->willReturn(true);

        self::assertTrue($this->orderConsolidationProvider->isConsolidationEnabledInSystem());
    }

    /**
     * Test consolidation not enabled in system settings scope
     * @return void
     * @dataProvider consolidiationForOrderProvider
     */
    public function testIsConsolidationEnabledForOrder(bool $expectedResult, bool $settings): void
    {
        $this->configManager
            ->expects(static::any())
            ->method('get')
            ->willReturn($settings);

        self::assertEquals($expectedResult, $this->orderConsolidationProvider->isConsolidationEnabledForOrder($this->entity));
    }


    /**
     * data provider for testing different settings
     * @return array
     */
    protected function consolidiationForOrderProvider(): array
    {
        return [
            [
                'expectedResult' => true,
                'settings'  => true
            ],
            [
                'expectedResult' => false,
                'settings'  => false
            ]
        ];
    }
}
