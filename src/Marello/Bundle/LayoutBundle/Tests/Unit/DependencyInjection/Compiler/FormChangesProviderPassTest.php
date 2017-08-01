<?php

namespace Marello\Bundle\LayoutBundle\Tests\Unit\DependencyInjection\Compiler;

use Marello\Bundle\LayoutBundle\DependencyInjection\Compiler\FormChangesProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FormChangesProviderPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormChangesProviderPass
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

        $this->compilerPass = new FormChangesProviderPass();
    }

    public function testProcessCompositeDoesNotExist()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(FormChangesProviderPass::COMPOSITE_SERVICE)
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
            ->with(FormChangesProviderPass::COMPOSITE_SERVICE)
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
            ->with(FormChangesProviderPass::COMPOSITE_SERVICE)
            ->willReturn(true);

        $registryServiceDefinition = $this->createMock('Symfony\Component\DependencyInjection\Definition');

        $this->containerBuilder
            ->expects($this->once())
            ->method('getDefinition')
            ->with(FormChangesProviderPass::COMPOSITE_SERVICE)
            ->willReturn($registryServiceDefinition);

        $taggedServices = [
            'service.name.1' => [['class' =>'class1', 'type' => 'type1', 'priority' => 40]],
            'service.name.2' => [['class' =>'class1', 'type' => 'type2', 'priority' => 20]],
            'service.name.3' => [['class' =>'class1', 'type' => 'type3', 'priority' => 30]],
            'service.name.4' => [['class' =>'class1', 'type' => 'type4', 'priority' => 10]],
        ];

        $this->containerBuilder
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn($taggedServices);

        $registryServiceDefinition
            ->expects($this->exactly(4))
            ->method('addMethodCall')
            ->withConsecutive(
                ['addProvider', [new Reference('service.name.4'), 'class1', 'type4']],
                ['addProvider', [new Reference('service.name.2'), 'class1', 'type2']],
                ['addProvider', [new Reference('service.name.3'), 'class1', 'type3']],
                ['addProvider', [new Reference('service.name.1'), 'class1', 'type1']]
            );

        $this->compilerPass->process($this->containerBuilder);
    }
}
