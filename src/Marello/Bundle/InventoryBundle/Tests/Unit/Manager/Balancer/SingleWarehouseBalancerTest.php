<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Manager\Balancer;

use Doctrine\Common\Collections\ArrayCollection;

use Marello\Bundle\InventoryBundle\Manager\InventoryBalancerInterface;
use Marello\Bundle\InventoryBundle\Manager\InventoryManagerInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\InventoryBundle\Manager\Balancer\SingleWarehouseBalancer;

use Marello\Bundle\ProductBundle\Entity\Product;

class SingleWarehouseBalancerTest extends \PHPUnit_Framework_TestCase
{
    /** @var InventoryUpdateContext $inventoryUpdateContext */
    protected $inventoryUpdateContext;

    /** @var InventoryBalancerInterface $balancer */
    protected $balancer;

    public function setUp()
    {
        $this->inventoryUpdateContext = $this->getMock(InventoryUpdateContext::class);
        $inventoryManager = $this->getMockBuilder(InventoryManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->balancer = new SingleWarehouseBalancer($inventoryManager);
        $this->balancer->setInventoryUpdateContext($this->inventoryUpdateContext);
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
     * @expectedExceptionMessage Cannot get inventory items, value for product is not an instance of Marello\Bundle\ProductBundle\Entity\Product
     */
    public function testBalanceInventoryNoProductSetException()
    {
        self::callMethod(
            $this->balancer,
            'balanceInventory',
            [$this->inventoryUpdateContext]
        );
    }

    public function testBalanceInventory()
    {
        $product = $this->getMock(Product::class);
        $this->inventoryUpdateContext
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);

        $inventoryItem = $this
            ->getMockBuilder(InventoryItem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $product
            ->expects($this->once())
            ->method('getInventoryItems')
            ->willReturn(new ArrayCollection([$inventoryItem]));

        $formattedItems[] = [
            'item' => $inventoryItem,
            'qty' => null,
            'allocatedQty' => null
        ];

        $this->inventoryUpdateContext
            ->expects($this->once())
            ->method('setItems')
            ->with($formattedItems);

        self::callMethod(
            $this->balancer,
            'balanceInventory',
            [$this->inventoryUpdateContext]
        );
    }
}
