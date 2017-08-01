<?php

namespace Marello\Bundle\OrderBundle\Tests\Unit\DependencyInjection\Compiler;

use Marello\Bundle\OrderBundle\DependencyInjection\Compiler\OrderItemDataProvidersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OrderItemDataProvidersPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderItemDataProvidersPass
     */
    protected $compilerPass;

    /**
     * @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $containerBuilder;

    protected function setUp()
    {
        $this->containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->compilerPass = new OrderItemDataProvidersPass();
    }

    public function testProcessCompositeDoesNotExist()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(OrderItemDataProvidersPass::COMPOSITE_SERVICE)
            ->willReturn(false);

        $this->containerBuilder
            ->expects($this->never())
            ->method('getDefinition');

        $this->containerBuilder
            ->expects($this->never())
            ->method('findTaggedServiceIds');

        $this->compilerPass->process($this->containerBuilder);
    }

    public function testProcessNoTaggedServicesFound()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(OrderItemDataProvidersPass::COMPOSITE_SERVICE)
            ->willReturn(true);

        $this->containerBuilder
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn([]);

        $this->containerBuilder
            ->expects($this->never())
            ->method('getDefinition');

        $this->compilerPass->process($this->containerBuilder);
    }

    public function testProcessWithTaggedServices()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(OrderItemDataProvidersPass::COMPOSITE_SERVICE)
            ->willReturn(true);

        $registryServiceDefinition = $this->createMock('Symfony\Component\DependencyInjection\Definition');

        $this->containerBuilder
            ->expects($this->once())
            ->method('getDefinition')
            ->with(OrderItemDataProvidersPass::COMPOSITE_SERVICE)
            ->willReturn($registryServiceDefinition);

        $taggedServices = [
            'service.name.1' => [['type' => 'type1', 'priority' => 40]],
            'service.name.2' => [['type' => 'type2', 'priority' => 10]],
            'service.name.3' => [['type' => 'type3', 'priority' => 20]],
            'service.name.4' => [['type' => 'type4', 'priority' => 30]],
        ];

        $this->containerBuilder
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn($taggedServices);

        $registryServiceDefinition
            ->expects($this->exactly(4))
            ->method('addMethodCall')
            ->withConsecutive(
                ['addProvider', ['type2', new Reference('service.name.2')]],
                ['addProvider', ['type3', new Reference('service.name.3')]],
                ['addProvider', ['type4', new Reference('service.name.4')]],
                ['addProvider', ['type1', new Reference('service.name.1')]]
            );

        $this->compilerPass->process($this->containerBuilder);
    }
}
