<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Marello\Bundle\ShippingBundle\Entity\Shipment;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataFactoryInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataProviderInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceIntegrationInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;
use Marello\Bundle\ShippingBundle\Method\ShippingMethodTypeInterface;
use Marello\Bundle\ShippingBundle\Workflow\ShipmentCreateAction;
use Oro\Bundle\ActionBundle\Model\ActionData;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

class ShipmentCreateActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextAccessor
     */
    protected $contextAccessor;

    /**
     * @var ShippingMethodProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingMethodProvider;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrine;

    /**
     * @var ShipmentCreateAction
     */
    protected $action;

    protected function setUp()
    {
        $this->contextAccessor = new ContextAccessor();
        $this->shippingMethodProvider = $this->createMock(ShippingMethodProviderInterface::class);
        $this->doctrine = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject $eventDispatcher */
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

        $this->assertAttributeEquals($options['context'], 'shippingContext', $this->action);
        $this->assertAttributeEquals($options['method'], 'method', $this->action);
        $this->assertAttributeEquals($options['methodType'], 'methodType', $this->action);
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\InvalidParameterException
     * @expectedExceptionMessage context parameter is required
     */
    public function testInitializeNoContextOption()
    {
        $options = [
            'method' => new PropertyPath('method'),
            'methodType' => new PropertyPath('methodType'),
        ];

        $this->action->initialize($options);
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\InvalidParameterException
     * @expectedExceptionMessage method parameter is required
     */
    public function testInitializeNoMethodOption()
    {
        $options = [
            'context' => new PropertyPath('context'),
            'methodType' => new PropertyPath('methodType'),
        ];

        $this->action->initialize($options);
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\InvalidParameterException
     * @expectedExceptionMessage methodType parameter is required
     */
    public function testInitializeNoMethodTypeOption()
    {
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
                'context' => new PropertyPath('context'),
                'method' => new PropertyPath('method'),
                'methodType' => new PropertyPath('methodType'),
            ]
        );

        $this->action->execute($context);
    }
}
