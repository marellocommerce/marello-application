<?php

namespace Marello\Bundle\InventoryBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProvider;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;

class OrderWarehousesProviderTest extends TestCase
{
    use EntityTrait;

    /**
     * @var OrderWarehousesProvider
     */
    protected $orderWarehousesProvider;

    protected function setUp(): void
    {
        $this->orderWarehousesProvider = new OrderWarehousesProvider();
    }

    /**
     * @dataProvider getWarehousesForOrderDataProvider
     *
     * @param Order $order
     * @param array $expectedResult
     */
    public function testGetWarehousesForOrder(Order $order, array $expectedResult)
    {
        $actualResult = $this->orderWarehousesProvider->getWarehousesForOrder($order);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function getWarehousesForOrderDataProvider()
    {
        $preferredSupplier = new Supplier();
        $preferredSupplier
            ->setCanDropship(true)
            ->setName('supplier1');
        $notPreferredSupplier = new Supplier();
        $notPreferredSupplier
            ->setCanDropship(true)
            ->setName('supplier2');

        $product1 = new Product();
        $product1->setSku('SKU-1');
        $product2 = new Product();
        $product2->setSku('SKU-2');
        $product3 = new Product();
        $product3->setSku('SKU-3');
        $product4 = new Product();
        $product4
            ->setSku('SKU-4')
            ->setPreferredSupplier($preferredSupplier);

        $inventoryItem1 = new InventoryItem($product1);
        $inventoryItem2 = new InventoryItem($product2);
        $inventoryItem3 = new InventoryItem($product3);
        $inventoryItem4 = new InventoryItem($product4);

        $externalWarehouseType = new WarehouseType(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);
        $globalWarehouseType = new WarehouseType(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_GLOBAL);

        /** @var Warehouse $defaultWarehouse */
        $defaultWarehouse = $this->getEntity(Warehouse::class, ['id' => 1]);
        $defaultWarehouse
            ->setDefault(true)
            ->setCode('default_warehouse')
            ->setWarehouseType($globalWarehouseType);
        /** @var Warehouse $notDefaultWarehouse */
        $notDefaultWarehouse = $this->getEntity(Warehouse::class, ['id' => 2]);
        $notDefaultWarehouse
            ->setCode('not_default_warehouse')
            ->setWarehouseType($globalWarehouseType);
        /** @var Warehouse $externalNotPreferableWarehouse */
        $externalNotPreferableWarehouse = $this->getEntity(Warehouse::class, ['id' => 3]);
        $externalNotPreferableWarehouse
            ->setCode('supplier2_external_warehouse')
            ->setWarehouseType($externalWarehouseType);
        /** @var Warehouse $externalPreferableWarehouse */
        $externalPreferableWarehouse = $this->getEntity(Warehouse::class, ['id' => 4]);
        $externalPreferableWarehouse
            ->setCode('supplier1_external_warehouse')
            ->setWarehouseType($externalWarehouseType);

        /** @var InventoryLevel $inventoryLevel1 */
        $inventoryLevel1 = $this->getEntity(InventoryLevel::class, ['id' => 1]);
        $inventoryLevel1
            ->setWarehouse($defaultWarehouse)
            ->setInventoryQty(3);
        /** @var InventoryLevel $inventoryLevel2 */
        $inventoryLevel2 = $this->getEntity(InventoryLevel::class, ['id' => 2]);
        $inventoryLevel2
            ->setWarehouse($notDefaultWarehouse)
            ->setInventoryQty(3);
        /** @var InventoryLevel $inventoryLevel3 */
        $inventoryLevel3 = $this->getEntity(InventoryLevel::class, ['id' => 3]);
        $inventoryLevel3
            ->setWarehouse($externalNotPreferableWarehouse)
            ->setInventoryQty(3);
        /** @var InventoryLevel $inventoryLevel4 */
        $inventoryLevel4 = $this->getEntity(InventoryLevel::class, ['id' => 4]);
        $inventoryLevel4
            ->setWarehouse($externalPreferableWarehouse)
            ->setInventoryQty(3);

        $inventoryItem1
            ->addInventoryLevel($inventoryLevel1)
            ->addInventoryLevel($inventoryLevel2);
        $inventoryItem2
            ->addInventoryLevel($inventoryLevel2);
        $inventoryItem3
            ->addInventoryLevel($inventoryLevel3)
            ->addInventoryLevel($inventoryLevel4);
        $inventoryItem4
            ->addInventoryLevel($inventoryLevel4);

        $orderItem1 = new OrderItem();
        $orderItem1
            ->setProduct($product1)
            ->setQuantity(2);
        $orderItem2 = new OrderItem();
        $orderItem2
            ->setProduct($product1)
            ->setQuantity(2);
        $orderItem3 = new OrderItem();
        $orderItem3
            ->setProduct($product2)
            ->setQuantity(2);
        $orderItem4 = new OrderItem();
        $orderItem4
            ->setProduct($product3)
            ->setQuantity(2);
        $orderItem5 = new OrderItem();
        $orderItem5
            ->setProduct($product4)
            ->setQuantity(2);
        $order = new Order();
        $order
            ->addItem($orderItem1)
            ->addItem($orderItem2)
            ->addItem($orderItem3)
            ->addItem($orderItem4)
            ->addItem($orderItem5);

        return [
            [
                'order' => $order,
                'expectedResult' => [
                    new OrderWarehouseResult([
                        OrderWarehouseResult::WAREHOUSE_FIELD => $defaultWarehouse,
                        OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem1])
                    ]),
                    new OrderWarehouseResult([
                        OrderWarehouseResult::WAREHOUSE_FIELD => $notDefaultWarehouse,
                        OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem2, $orderItem3])
                    ]),
                    new OrderWarehouseResult([
                        OrderWarehouseResult::WAREHOUSE_FIELD => $externalNotPreferableWarehouse,
                        OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem4])
                    ]),
                    new OrderWarehouseResult([
                        OrderWarehouseResult::WAREHOUSE_FIELD => $externalPreferableWarehouse,
                        OrderWarehouseResult::ORDER_ITEMS_FIELD => new ArrayCollection([$orderItem5])
                    ]),
                ]
            ]
        ];
    }
}
