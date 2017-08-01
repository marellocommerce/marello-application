<?php

namespace Marello\Bundle\TaxBundle\Tests\Unit\DependencyInjection\Compiler;

use Marello\Bundle\TaxBundle\DependencyInjection\Compiler\TaxRuleMatcherPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TaxRuleMatcherPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxRuleMatcherPass
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

        $this->compilerPass = new TaxRuleMatcherPass();
    }

    public function testProcessCompositeDoesNotExist()
    {
        $this->containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with(TaxRuleMatcherPass::COMPOSITE_SERVICE)
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
            ->with(TaxRuleMatcherPass::COMPOSITE_SERVICE)
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
            ->with(TaxRuleMatcherPass::COMPOSITE_SERVICE)
            ->willReturn(true);

        $registryServiceDefinition = $this->createMock('Symfony\Component\DependencyInjection\Definition');

        $this->containerBuilder
            ->expects($this->once())
            ->method('getDefinition')
            ->with(TaxRuleMatcherPass::COMPOSITE_SERVICE)
            ->willReturn($registryServiceDefinition);

        $taggedServices = [
            'service.name.1' => [['priority' => 30]],
            'service.name.2' => [['priority' => 10]],
            'service.name.3' => [['priority' => 40]],
            'service.name.4' => [['priority' => 20]],
        ];

        $this->containerBuilder
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn($taggedServices);

        $registryServiceDefinition
            ->expects($this->exactly(4))
            ->method('addMethodCall')
            ->withConsecutive(
                ['addMatcher', [new Reference('service.name.2')]],
                ['addMatcher', [new Reference('service.name.4')]],
                ['addMatcher', [new Reference('service.name.1')]],
                ['addMatcher', [new Reference('service.name.3')]]
            );

        $this->compilerPass->process($this->containerBuilder);
    }
}
