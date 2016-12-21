<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Manager\Balancer;

use Marello\Bundle\InventoryBundle\Manager\Balancer\AbstractInventoryBalancer;
use Marello\Bundle\InventoryBundle\Manager\InventoryBalancerInterface;
use Marello\Bundle\InventoryBundle\Manager\InventoryManagerInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class AbstractInventoryBalancerTest extends \PHPUnit_Framework_TestCase
{
    /** @var InventoryUpdateContext $inventoryUpdateContext */
    protected $inventoryUpdateContext;

    /** @var InventoryBalancerInterface $balancer */
    protected $balancer;

    /** @var InventoryManagerInterface $inventoryManager */
    protected $inventoryManager;

    public function setUp()
    {
        $this->inventoryUpdateContext = $this->getMock(InventoryUpdateContext::class);
        $this->inventoryManager = $this->getMockBuilder(InventoryManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->balancer = $this->getMockBuilder(AbstractInventoryBalancer::class)
            ->setConstructorArgs(array($this->inventoryManager))
            ->setMethods(['canBalance', 'canUpdateInventory', 'getInventoryManager'])
            ->getMockForAbstractClass();
    }

    public function tearDown()
    {
        unset($this->balancer);
        unset($this->inventoryManager);
        unset($this->inventoryUpdateContext);
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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot process without a context being set, please call setInventoryUpdateContext before calling process
     */
    public function testProcessNoContextSetException()
    {
        $this->assertAttributeEmpty('context', $this->balancer);
        $this->balancer->process();
    }

    /**
     * test setting of context on balancer
     */
    public function testSetInventoryUpdateContext()
    {
        $this->balancer->setInventoryUpdateContext($this->inventoryUpdateContext);
        $this->assertAttributeEquals($this->inventoryUpdateContext, 'context', $this->balancer);
    }

    /**
     * test process scenario's
     * @param $canBalance
     * @param $canUpdate
     * @dataProvider getProcessDataProvider
     */
    public function testProcess($canBalance, $canUpdate = null)
    {
        $this->balancer->setInventoryUpdateContext($this->inventoryUpdateContext);
        $this->balancer->expects($this->once())
            ->method('canBalance')
            ->willReturn($canBalance);

        if ($canBalance && $canUpdate) {
            $this->balancer->expects($this->once())
                ->method('canUpdateInventory')
                ->willReturn(true);

            $this->balancer->expects($this->once())
                ->method('balanceInventory');

            $this->balancer->expects($this->once())
                ->method('getInventoryManager')
                ->willReturn($this->inventoryManager);
        } elseif ($canBalance && !$canUpdate) {
            $this->balancer->expects($this->once())
                ->method('canUpdateInventory')
                ->willReturn($canUpdate);
        } else {
            $this->balancer->expects($this->never())
                ->method('canUpdateInventory');

            $this->balancer->expects($this->never())
                ->method('balanceInventory');

            $this->balancer->expects($this->never())
                ->method('getInventoryManager');
        }

        $this->balancer->process();
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getProcessDataProvider()
    {
        return array(
            'cannot balance' => array(
                'canBalance' => false
            ),
            'can balance but not update' => array(
                'canBalance' => true,
                'canUpdate' => false
            ),
            'can balance and update' => array(
                'canBalance' => true,
                'canUpdate' => true
            ),
        );
    }
}
