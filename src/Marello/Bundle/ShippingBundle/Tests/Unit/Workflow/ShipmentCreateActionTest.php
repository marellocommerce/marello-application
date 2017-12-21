<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\Workflow;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataFactoryInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceDataProviderInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceIntegrationInterface;
use Marello\Bundle\ShippingBundle\Integration\ShippingServiceRegistry;
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
     * @var ShippingServiceRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

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
        $this->registry = $this->createMock(ShippingServiceRegistry::class);
        $this->doctrine = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject $eventDispatcher */
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->action = new ShipmentCreateAction($this->contextAccessor, $this->registry, $this->doctrine);
        $this->action->setDispatcher($eventDispatcher);
    }

    public function testInitialize()
    {
        $options = [
            'entity' => new PropertyPath('entity'),
            'service' => new PropertyPath('service'),
        ];

        $this->assertInstanceOf(
            'Oro\Component\Action\Action\ActionInterface',
            $this->action->initialize($options)
        );

        $this->assertAttributeEquals($options['entity'], 'entity', $this->action);
        $this->assertAttributeEquals($options['service'], 'service', $this->action);
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\InvalidParameterException
     * @expectedExceptionMessage Entity parameter is required
     */
    public function testInitializeNoEntityOption()
    {
        $options = [
            'service' => new PropertyPath('service'),
        ];

        $this->action->initialize($options);
    }

    /**
     * @expectedException \Oro\Component\Action\Exception\InvalidParameterException
     * @expectedExceptionMessage Service parameter is required
     */
    public function testInitializeNoServiceOption()
    {
        $options = [
            'entity' => new PropertyPath('entity'),
        ];

        $this->action->initialize($options);
    }

    /**
     * @dataProvider executeDataProvider
     *
     * @param $registryHasService
     * @param $inputService
     * @param $expectedService
     * @throws \Oro\Component\Action\Exception\InvalidParameterException
     */
    public function testExecute($registryHasService, $inputService, $expectedService)
    {
        $entity = new Order();

        $metaData = $this->createMock(ClassMetadata::class);
        $metaData->expects(static::once())
            ->method('getName')
            ->willReturn('name');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(static::once())
            ->method('getClassMetadata')
            ->willReturn($metaData);

        $this->doctrine->expects(static::once())
            ->method('getManager')
            ->willReturn($em);

        $context = new ActionData(
            [
                'entity' => $entity,
                'service' => $inputService,
            ]
        );
        $dataProvider = $this->createMock(ShippingServiceDataProviderInterface::class);
        $dataProvider->expects(static::once())
            ->method('setEntity')
            ->with($entity)
            ->willReturnSelf();
        $dataFactory = $this->createMock(ShippingServiceDataFactoryInterface::class);
        $dataFactory->expects(static::once())
            ->method('createData')
            ->with($dataProvider)
            ->willReturn([]);
        $integration = $this->createMock(ShippingServiceIntegrationInterface::class);
        $integration->expects(static::once())
            ->method('createShipment')
            ->with($entity, []);

        $this->registry->expects(static::any())
            ->method('hasDataFactory')
            ->with($inputService)
            ->willReturn($registryHasService);

        $this->registry->expects(static::any())
            ->method('hasIntegration')
            ->with($inputService)
            ->willReturn($registryHasService);

        $this->registry->expects(static::once())
            ->method('getDataFactory')
            ->with($expectedService)
            ->willReturn($dataFactory);

        $this->registry->expects(static::once())
            ->method('getIntegration')
            ->with($expectedService)
            ->willReturn($integration);

        $this->registry->expects(static::once())
            ->method('getDataProvider')
            ->with('name')
            ->willReturn($dataProvider);

        $this->action->initialize(
            [
                'entity' => new PropertyPath('entity'),
                'service' => new PropertyPath('service'),
            ]
        );

        $this->action->execute($context);
    }

    public function executeDataProvider()
    {
        return [
            [
                'registryHasService' => true,
                'inputService' => 'ups',
                'expectedService' => 'ups',
            ],
            [
                'registryHasService' => false,
                'inputService' => 'ups',
                'expectedService' => ShipmentCreateAction::DEFAULT_SHIPPING_SERVICE,
            ]
        ];
    }
}
