<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\ShippingBundle\Context\ShippingContext;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\ActionBundle\Model\ActionData;
use Oro\Component\ConfigExpression\ContextAccessor;

use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Workflow\ShipmentCreateAction;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodTypeInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;

class ShipmentCreateActionTest extends TestCase
{
    /**
     * @var ContextAccessor
     */
    protected $contextAccessor;

    /**
     * @var ShippingMethodProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shippingMethodProvider;

    /**
     * @var Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $doctrine;

    /**
     * @var ShipmentCreateAction
     */
    protected $action;

    protected function setUp(): void
    {
        $this->contextAccessor = new ContextAccessor();
        $this->shippingMethodProvider = $this->createMock(ShippingMethodProviderInterface::class);
        $this->doctrine = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject $eventDispatcher */
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->action = new ShipmentCreateAction(
            $this->contextAccessor,
            $this->doctrine,
            $this->shippingMethodProvider
        );
        $this->action->setDispatcher($eventDispatcher);
    }

    public function testInitialize()
    {
        $options = [
            'context' => new PropertyPath('context'),
            'method' => new PropertyPath('method'),
            'methodType' => new PropertyPath('methodType'),
        ];

        $this->assertInstanceOf(
            'Oro\Component\Action\Action\ActionInterface',
            $this->action->initialize($options)
        );
    }

    public function testInitializeNoContextOption()
    {
        $this->expectException(\Oro\Component\Action\Exception\InvalidParameterException::class);
        $this->expectExceptionMessage('context parameter is required');
        $options = [
            'method' => new PropertyPath('method'),
            'methodType' => new PropertyPath('methodType'),
        ];

        $this->action->initialize($options);
    }

    public function testInitializeNoMethodOption()
    {
        $this->expectException(\Oro\Component\Action\Exception\InvalidParameterException::class);
        $this->expectExceptionMessage('method parameter is required');
        $options = [
            'context' => new PropertyPath('context'),
            'methodType' => new PropertyPath('methodType'),
        ];

        $this->action->initialize($options);
    }

    public function testInitializeNoMethodTypeOption()
    {
        $this->expectException(\Oro\Component\Action\Exception\InvalidParameterException::class);
        $this->expectExceptionMessage('methodType parameter is required');
        $options = [
            'context' => new PropertyPath('context'),
            'method' => new PropertyPath('method'),
        ];

        $this->action->initialize($options);
    }

    /**
     * @throws \Oro\Component\Action\Exception\InvalidParameterException
     */
    public function testExecute()
    {
        $entity = $this->createMock(ShippingContextInterface::class);
        $context = new ActionData(
            [
                'context' => $entity,
                'method' => 'manual_shipping',
                'methodType' => 'primary'
            ]
        );

        $entityManager = $this->createMock(ObjectManager::class);
        $shippingMethod = $this->createMock(ShippingMethodInterface::class);
        $shippingMethodType = $this->createMock(ShippingMethodTypeInterface::class);
        $shipment = new Shipment();

        $entityManager->expects(static::once())
            ->method('persist')
            ->with($shipment);
        $entityManager->expects(static::once())
            ->method('flush');
        $shippingMethod->expects(static::once())
            ->method('getType')
            ->willReturn($shippingMethodType);
        $shippingMethodType->expects(static::once())
            ->method('createShipment')
            ->willReturn($shipment);
        $this->shippingMethodProvider->expects(static::once())
            ->method('getShippingMethod')
            ->willReturn($shippingMethod);
        $this->doctrine->expects(static::once())
            ->method('getManagerForClass')
            ->willReturn($entityManager);

        $this->action->initialize(
            [
                'context' => [$this->createMock(ShippingContextInterface::class)],
                'method' => new PropertyPath('method'),
                'methodType' => new PropertyPath('methodType'),
            ]
        );

        $this->action->execute($context);
    }
}
