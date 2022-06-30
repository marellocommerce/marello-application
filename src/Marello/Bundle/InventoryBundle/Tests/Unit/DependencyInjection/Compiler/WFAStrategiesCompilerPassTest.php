<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\InventoryBundle\DependencyInjection\CompilerPass\WFAStrategiesCompilerPass;

class WFAStrategiesCompilerPassTest extends TestCase
{
    /**
     * @var WFAStrategiesCompilerPass
     */
    protected $compilerPass;

    /**
     * @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $containerBuilder;

    public function setUp(): void
    {
        $this->containerBuilder = $this->createMock(ContainerBuilder::class);

        $this->compilerPass = new WFAStrategiesCompilerPass();
    }

    public function tearDown(): void
    {
        unset($this->compilerPass, $this->containerBuilder);
    }

    public function testProcessRegistryDoesNotExist()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(WFAStrategiesCompilerPass::SERVICE)
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
            ->with(WFAStrategiesCompilerPass::SERVICE)
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
            ->with(WFAStrategiesCompilerPass::SERVICE)
            ->willReturn(true);

        $registryServiceDefinition = $this->createMock('Symfony\Component\DependencyInjection\Definition');

        $this->containerBuilder
            ->expects($this->once())
            ->method('getDefinition')
            ->with(WFAStrategiesCompilerPass::SERVICE)
            ->willReturn($registryServiceDefinition);

        $taggedServices = [
            'service.name.1' => [[]],
            'service.name.2' => [[]],
            'service.name.3' => [[]],
            'service.name.4' => [[]],
        ];

        $this->containerBuilder
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn($taggedServices);

        $registryServiceDefinition
            ->expects($this->exactly(4))
            ->method('addMethodCall')
            ->withConsecutive(
                ['addStrategy', [new Reference('service.name.1')]],
                ['addStrategy', [new Reference('service.name.2')]],
                ['addStrategy', [new Reference('service.name.3')]],
                ['addStrategy', [new Reference('service.name.4')]]
            );

        $this->compilerPass->process($this->containerBuilder);
    }
}
