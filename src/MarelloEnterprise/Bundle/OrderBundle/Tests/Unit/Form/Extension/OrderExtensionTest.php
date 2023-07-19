<?php

namespace MarelloEnterprise\Bundle\OrderBundle\Tests\Unit\Form\Extension;

use Doctrine\Persistence\ObjectManager;

use Oro\Bundle\ConfigBundle\Event\ConfigGetEvent;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
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
     * Test Consolidation Enabled
     * @return void
     */
    public function testPostDataListenerConsolidationEnabled()
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
            ->willReturnOnConsecutiveCalls(true, true, false);

        $formMock = $this->createMock(FormInterface::class);
        $formConfigMock = $this->createMock(FormConfigInterface::class);
        $formMock->expects(static::once())
            ->method('getConfig')
            ->willReturn($formConfigMock);
        $formConfigMock->expects(static::once())
            ->method('getOptions')
            ->willReturn([]);

        $resolvedFormTypeMock = $this->createMock(ResolvedFormTypeInterface::class);
        $formConfigMock->expects(static::once())
            ->method('getType')
            ->willReturn($resolvedFormTypeMock);

        $resolvedFormTypeMock->expects(static::once())
            ->method('getInnerType')
            ->willReturn($this->createMock(FormTypeInterface::class));
        $this->form
            ->expects(static::once())
            ->method('get')
            ->with('consolidation_enabled')
            ->willReturn($formMock);

        $this->form
            ->expects(static::once())
            ->method('add');

        $this->orderExtension->postSetDataListener($this->formEvent);
    }

    /**
     * Test consolidation not enabled
     * @return void
     */
    public function testPostDataListenerConsolidationDisabled()
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
            ->willReturnOnConsecutiveCalls(true, false, false);

        $formMock = $this->createMock(FormInterface::class);
        $formConfigMock = $this->createMock(FormConfigInterface::class);
        $formMock->expects(static::never())
            ->method('getConfig')
            ->willReturn($formConfigMock);
        $formConfigMock->expects(self::never())
            ->method('getOptions')
            ->willReturn([]);

        $resolvedFormTypeMock = $this->createMock(ResolvedFormTypeInterface::class);
        $formConfigMock->expects(self::never())
            ->method('getType')
            ->willReturn($resolvedFormTypeMock);

        $resolvedFormTypeMock->expects(self::never())
            ->method('getInnerType')
            ->willReturn($this->createMock(FormTypeInterface::class));
        $this->form
            ->expects(static::never())
            ->method('get')
            ->with('consolidation_enabled')
            ->willReturn($formMock);

        $this->form
            ->expects(static::never())
            ->method('add');

        $this->orderExtension->postSetDataListener($this->formEvent);
    }

    /**
     * Test consolidation feature not enabled
     * @return void
     */
    public function testConsolidationFeatureDisabled()
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
            ->willReturnOnConsecutiveCalls(false, false, true);

        $this->form
            ->expects(static::once())
            ->method('remove')
            ->with('consolidation_enabled');

        $this->form
            ->expects(static::never())
            ->method('add');

        $this->orderExtension->postSetDataListener($this->formEvent);
    }
}
