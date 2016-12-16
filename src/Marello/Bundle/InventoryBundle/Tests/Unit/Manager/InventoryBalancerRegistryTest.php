<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Marello\Bundle\InventoryBundle\Manager\InventoryBalancerRegistry;
use Marello\Bundle\InventoryBundle\Manager\InventoryBalancerInterface;

class InventoryBalancerRegistryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerInterface $container */
    protected $container;

    /** @var InventoryBalancerRegistry $registry */
    protected $registry;

    /** @var string  */
    protected $alias = 'test_alias';

    /** @var $balancerClass */
    protected $balancerClass = InventoryBalancerInterface::class;

    public function setUp()
    {
        $this->container = $this->getMock(ContainerInterface::class);
        $this->registry = new InventoryBalancerRegistry($this->container);
    }

    /**
     * Call protected methods for testing
     * @param $obj
     * @param $name
     * @param array $args
     * @return mixed
     */
    protected static function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    public function testRegisterBalancer()
    {
        $this->registry->registerInventoryBalancer($this->alias, $this->balancerClass);

        $balancers = $this->registry->getRegisteredInventoryBalancers();
        $this->assertCount(1, $balancers);
        $this->assertEquals($this->balancerClass, $balancers->get($this->alias));

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Trying to redeclare balancer alias "test_alias".
     */
    public function testRegisterBalancerWithSameAlias()
    {
        // register alias "test_alias" for the first time
        $this->registry->registerInventoryBalancer($this->alias, $this->balancerClass);
        $balancers = $this->registry->getRegisteredInventoryBalancers();
        $this->assertCount(1, $balancers);
        $this->assertEquals($this->balancerClass, $balancers->get($this->alias));

        // register alias "test_alias" for the second time
        $this->registry->registerInventoryBalancer($this->alias, $this->balancerClass);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No registered balancer found for alias "test_alias".
     */
    public function testGetInventoryBalancerNotFound()
    {
        $this->assertCount(0, $this->registry->getRegisteredInventoryBalancers());

        $this->registry->getInventoryBalancer($this->alias);
    }

    /**
     * {@inheritdoc}
     */
    public function testGetInventoryBalancer()
    {
        $this->registry->registerInventoryBalancer($this->alias, $this->balancerClass);
        $balancers = $this->registry->getRegisteredInventoryBalancers();
        $this->assertCount(1, $balancers);

        $this->container->expects($this->once())
            ->method('get')
            ->with($balancers->get($this->alias))
            ->willReturn($this->balancerClass);

        $this->assertEquals($this->balancerClass, $this->registry->getInventoryBalancer($this->alias));
    }
}
