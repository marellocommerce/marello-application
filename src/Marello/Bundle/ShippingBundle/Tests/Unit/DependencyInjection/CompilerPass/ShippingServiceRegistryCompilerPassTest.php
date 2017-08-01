<?php

namespace Marello\Bundle\ShippingBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Marello\Bundle\ShippingBundle\DependencyInjection\CompilerPass\ShippingServiceRegistryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ShippingServiceRegistryCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShippingServiceRegistryCompilerPass
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

        $this->compilerPass = new ShippingServiceRegistryCompilerPass();
    }

    public function testProcessCompositeDoesNotExist()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(ShippingServiceRegistryCompilerPass::REGISTRY_SERVICE_ID)
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
            ->with(ShippingServiceRegistryCompilerPass::REGISTRY_SERVICE_ID)
            ->willReturn(true);

        $this->containerBuilder
            ->expects($this->exactly(3))
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
            ->with(ShippingServiceRegistryCompilerPass::REGISTRY_SERVICE_ID)
            ->willReturn(true);

        $registryServiceDefinition = $this->createMock('Symfony\Component\DependencyInjection\Definition');

        $this->containerBuilder
            ->expects($this->once())
            ->method('getDefinition')
            ->with(ShippingServiceRegistryCompilerPass::REGISTRY_SERVICE_ID)
            ->willReturn($registryServiceDefinition);

        $integrationServices = [
            'integration.name.1' => [['alias' => 'type1']],
            'integration.name.2' => [['alias' => 'type2']],
            'integration.name.3' => [['alias' => 'type3']],
        ];

        $factoryServices = [
            'factory.name.1' => [['alias' => 'type1']],
            'factory.name.2' => [['alias' => 'type2']],
            'factory.name.3' => [['alias' => 'type3']],
        ];

        $providerServices = [
            'provider.name.1' => [['class' => 'entity1']],
            'provider.name.2' => [['class' => 'entity2']],
        ];

        $this->containerBuilder
            ->expects($this->exactly(3))
            ->method('findTaggedServiceIds')
            ->willReturnOnConsecutiveCalls($integrationServices, $factoryServices, $providerServices);

        $registryServiceDefinition
            ->expects($this->exactly(8))
            ->method('addMethodCall')
            ->withConsecutive(
                ['registerIntegration', ['type1', new Reference('integration.name.1')]],
                ['registerIntegration', ['type2', new Reference('integration.name.2')]],
                ['registerIntegration', ['type3', new Reference('integration.name.3')]],
                ['registerDataFactory', ['type1', new Reference('factory.name.1')]],
                ['registerDataFactory', ['type2', new Reference('factory.name.2')]],
                ['registerDataFactory', ['type3', new Reference('factory.name.3')]],
                ['registerDataProvider', ['entity1', new Reference('provider.name.1')]],
                ['registerDataProvider', ['entity2', new Reference('provider.name.2')]]
            );

        $this->compilerPass->process($this->containerBuilder);
    }
}
