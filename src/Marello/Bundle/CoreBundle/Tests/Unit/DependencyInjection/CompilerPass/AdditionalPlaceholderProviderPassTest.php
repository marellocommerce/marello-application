<?php

namespace Marello\Bundle\CoreBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use PHPUnit\Framework\TestCase;

use Marello\Bundle\CoreBundle\DependencyInjection\CompilerPass\AdditionalPlaceholderProviderPass;

class AdditionalPlaceholderProviderPassTest extends TestCase
{
    /**
     * @var AdditionalPlaceholderProviderPass
     */
    protected $compilerPass;

    /**
     * @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $containerBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->compilerPass = new AdditionalPlaceholderProviderPass();
    }

    /**
     * {@inheritdoc}
     */
    public function testProcessServiceDoesNotExist()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(AdditionalPlaceholderProviderPass::PROVIDER_SERVICE)
            ->willReturn(false);

        $this->containerBuilder
            ->expects($this->never())
            ->method('getDefinition');

        $this->containerBuilder
            ->expects($this->never())
            ->method('findTaggedServiceIds');

        $this->compilerPass->process($this->containerBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function testProcessNoTaggedServicesFound()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(AdditionalPlaceholderProviderPass::PROVIDER_SERVICE)
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

    /**
     * {@inheritdoc}
     */
    public function testProcessWithTaggedServices()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(AdditionalPlaceholderProviderPass::PROVIDER_SERVICE)
            ->willReturn(true);

        $taggedServices = [
            'service.name.1' => [],
            'service.name.2' => [],
            'service.name.3' => [],
            'service.name.4' => [],
        ];

        $this->containerBuilder
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn($taggedServices);

        $registryServiceDefinition = $this->createMock('Symfony\Component\DependencyInjection\Definition');

        $this->containerBuilder
            ->expects($this->once())
            ->method('getDefinition')
            ->with(AdditionalPlaceholderProviderPass::PROVIDER_SERVICE)
            ->willReturn($registryServiceDefinition);

        $registryServiceDefinition
            ->expects($this->exactly(4))
            ->method('addMethodCall')
            ->withConsecutive(
                ['addAdditionalPlaceholderDataProvider', [new Reference('service.name.1')]],
                ['addAdditionalPlaceholderDataProvider', [new Reference('service.name.2')]],
                ['addAdditionalPlaceholderDataProvider', [new Reference('service.name.3')]],
                ['addAdditionalPlaceholderDataProvider', [new Reference('service.name.4')]]
            );

        $this->compilerPass->process($this->containerBuilder);
    }
}
