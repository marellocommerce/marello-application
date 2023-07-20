<?php

namespace MarelloEnterprise\Bundle\OrderBundle\Tests\Unit\Form\Extension;

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

class OrderExtensionTest extends TestCase
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
     * @var OrderExtension
     */
    protected $orderExtension;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->form = $this->createMock(FormInterface::class);
        $this->entity = $this->createMock(Order::class);
        $this->salesChannel = $this->createMock(SalesChannel::class);
        $this->entity
            ->expects(static::any())
            ->method('getSalesChannel')
            ->willReturn($this->salesChannel);
        $this->formEvent = $this->createMock(FormEvent::class);
        $this->formEvent
            ->expects(static::once())
            ->method('getData')
            ->willReturn($this->entity);
        $this->formEvent
            ->expects(static::once())
            ->method('getForm')
            ->willReturn($this->form);

        $this->configManager = $this->createMock(ConfigManager::class);
        $this->orderExtension = new OrderExtension($this->configManager);
    }

    /**
     * Test Consolidation Enabled on SalesChannel
     * @return void
     */
    public function testConsolidationEnabledForSalesChannel(): void
    {
        $this->settingSetupTest(true, true, false);
        $this->testFormIsUpdated();
        $this->orderExtension->postSetDataListener($this->formEvent);
    }

    /**
     * Test Consolidation Enabled on SalesChannel
     * @return void
     */
    public function testConsolidationEnabledForSystem(): void
    {
        $this->settingSetupTest(true, false, true);
        $this->testFormIsUpdated();
        $this->orderExtension->postSetDataListener($this->formEvent);
    }

    /**
     * Test consolidation not enabled
     * @return void
     */
    public function testConsolidationDisabled(): void
    {
        $this->settingSetupTest(true, false, false);
        $this->testFormIsUpdated(false, true);
        $this->orderExtension->postSetDataListener($this->formEvent);
    }

    /**
     * Test consolidation feature not enabled at all
     * @return void
     */
    public function testConsolidationFeatureDisabled(): void
    {
        $this->settingSetupTest(false, false, true);
        $this->testFormIsUpdated(false);
        $this->orderExtension->postSetDataListener($this->formEvent);
    }

    /**
     * @param bool $featureEnabled
     * @param bool $salesChannelEnabled
     * @param bool $systemEnabled
     * @return void
     */
    protected function settingSetupTest(bool $featureEnabled, bool $salesChannelEnabled, bool $systemEnabled): void
    {
        $this->configManager
            ->expects(static::exactly(3))
            ->method('get')
            ->withConsecutive(
                ['marello_enterprise_order.enable_order_consolidation'],
                ['marello_enterprise_order.set_consolidation_for_scope',
                    false,
                    false,
                    $this->salesChannel
                ],
                ['marello_enterprise_order.set_consolidation_for_scope']
            )
            ->willReturnOnConsecutiveCalls($featureEnabled, $salesChannelEnabled, $systemEnabled);
    }

    /**
     * @param bool $consolidationFieldAdded
     * @param bool $allDisabled
     * @return void
     */
    protected function testFormIsUpdated(bool $consolidationFieldAdded = true, bool $allDisabled = false): void
    {
        $addedMethodsCalled = $consolidationFieldAdded ? 1 : 0;
        $removedMethodsCalled = $consolidationFieldAdded ? 0 : 1;
        if ($allDisabled) {
            $removedMethodsCalled = 0;
        }

        $formMock = $this->createMock(FormInterface::class);
        $formConfigMock = $this->createMock(FormConfigInterface::class);
        $resolvedFormTypeMock = $this->createMock(ResolvedFormTypeInterface::class);
        $this->form
            ->expects(static::exactly($addedMethodsCalled))
            ->method('get')
            ->with('consolidation_enabled')
            ->willReturn($formMock);

        $formMock->expects(static::exactly($addedMethodsCalled))
            ->method('getConfig')
            ->willReturn($formConfigMock);

        $formConfigMock->expects(static::exactly($addedMethodsCalled))
            ->method('getOptions')
            ->willReturn([]);

        $formConfigMock->expects(static::exactly($addedMethodsCalled))
            ->method('getType')
            ->willReturn($resolvedFormTypeMock);

        $resolvedFormTypeMock->expects(static::exactly($addedMethodsCalled))
            ->method('getInnerType')
            ->willReturn($this->createMock(FormTypeInterface::class));

        $this->form
            ->expects(static::exactly($removedMethodsCalled))
            ->method('remove')
            ->with('consolidation_enabled');

        $this->form
            ->expects(static::exactly($addedMethodsCalled))
            ->method('add');
    }
}
