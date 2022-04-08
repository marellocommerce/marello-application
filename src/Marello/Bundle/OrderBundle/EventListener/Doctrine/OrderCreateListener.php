<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Model\OrderWarehouseResult;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Model\OrderStatusesInterface;

class OrderCreateListener
{
    const CONSOLIDATION = true;
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /** @var OrderWarehousesProviderInterface $warehousesProvider */
    protected $warehousesProvider;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        OrderWarehousesProviderInterface $warehousesProvider
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->warehousesProvider = $warehousesProvider;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Order) {
            if (self::CONSOLIDATION && $entity->getItems()->count() > 1) {
                // consolidation logic
                $consolidationWHs = $this->getConsolidationWarehouses($entity);
                /** @var Warehouse $consoWH */
                $consoWH = $this->findConsolidationWarehouse($consolidationWHs, $entity->getItems());
                $consoWH = $consolidationWHs->first();
                // now we have figure out what to do next
                // filtering items :thinking:

                // this will get the 'normal' result for allocation
                $result = $this->warehousesProvider->getWarehousesForOrder($entity);
                foreach ($result as $k => $warehouseResult) {
                    if ($consoWH === $warehouseResult->getWarehouse() ||
                        $warehouseResult->getWarehouse()->getWarehouseType()->getName() === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL)
                    {
                        continue;
                    } else {
                        // the items are not in the correct WH yet, so we need to get them to move
                        // to the conso warehouse by creating a replenishment order from the result wh,
                        // to the conso warehouse.
                        // create replenishment order
                        $warehouse = $warehouseResult->getWarehouse();
                        $items = $warehouseResult->getOrderItems();
                        $products = [];
//                        $replOrderConfig = new ReplenishmentOrderConfig();
//                        $replOrderConfig->setStrategy('equal_division');
//                        $replOrderConfig->setOrigins([$warehouse->getId()]);
//                        $replOrderConfig->setDestinations([$consoWH->getId()]);
//                        $replOrderConfig->setProducts($products);
//                        $replOrderConfig->setPercentage(100);
//                        $replcfgEm = $this->doctrineHelper->getEntityManagerForClass(ReplenishmentOrderConfig::class);
//                        $replcfgEm->persist($replOrderConfig);
//                        $replOrder = new ReplenishmentOrder();
//                        $replOrder->setOrigin($warehouse);
//                        $replOrder->setDestination($consoWH);
//                        $replOrder->setDescription('it should be shipped somewhere else...');
//                        $replOrder->setReplOrderConfig($replOrderConfig);
//                        $replOrder->setPercentage(100);
                        $items->map(function (OrderItem $item) use ($entity, $consoWH, $warehouse, $replOrder) {
                            // set warehouse on order item
                            $item->setWarehouse($consoWH);

                            // create replenishment item for the
//                            $products[] = $item->getProduct()->getId();
//                            $replitem = new ReplenishmentOrderItem();
//                            $replitem->setProduct($item->getProduct());
//                            $replitem->setInventoryQty($item->getQuantity());
//                            $replitem->setTotalInventoryQty($item->getQuantity());
//                            $replOrder->addReplOrderItem($replitem);
                        });

//                        $replEm = $this->doctrineHelper->getEntityManagerForClass(ReplenishmentOrder::class);
//                        $replEm->persist($replOrder);
                    }
                }
            } else {
                //$result = $this->warehousesProvider->getWarehousesForOrder($entity);
            }
        }
    }

    protected function getConsolidationWarehouses(Order $order)
    {
        /** @var WarehouseChannelGroupLink $linkOwner */
        $linkOwner = $this->doctrineHelper
            ->getEntityRepositoryForClass(WarehouseChannelGroupLink::class)
            ->findLinkBySalesChannelGroup($order->getSalesChannel()->getGroup());
        $consoWHs = new ArrayCollection();
        foreach ($linkOwner->getWarehouseGroup()->getWarehouses() as $warehouse) {
            if ($warehouse->getCode() === 'warehouse_de_1') {
            //if ('consolidationistrue' === 'consolidationistrue') {
                $consoWHs->add($warehouse);
            }
        }

        return $consoWHs;
    }

    protected function findConsolidationWarehouse($consolidationwhs, $items)
    {
        // the logic which warehouse is able to ship (consolidation wh)
        // can be a different solution where different conditions can be used
        // in order to determine the consolidation WH. In this case we used the 'who can ship the most items'

        $productsByWh = [];
        $productsQty = [];
        // find the warehouse where most of the items can be delivered
        // this can be from stock on hand, order on demand, back-and pre-order, but excluding dropshipping
        foreach ($items as $index => $orderItem) {
            /** @var Product $product */
            $product = $orderItem->getProduct();
            $sku = $product->getSku();
            $key = sprintf('%s_|_%s', $sku, $index);
            $orderItemsByProducts[$key] = $orderItem;
            $invLevToWh = [];
            $invLevelQtyKey = null;
            $inventoryItem = $product->getInventoryItems()->first();
            /** @var InventoryLevel $inventoryLevel */
            foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                $invLevelQtyKey = sprintf('%s_|_%s', $sku, $inventoryLevel->getId());
                $invLevelQty = $inventoryLevel->getVirtualInventoryQty();
                if (isset($productsQty[$invLevelQtyKey])) {
                    $invLevelQty = $invLevelQty - $productsQty[$invLevelQtyKey];
                }
                $warehouse = $inventoryLevel->getWarehouse();
                $invLevToWh[$warehouse->getId()] = $inventoryLevel;
                if ($consolidationwhs->contains($warehouse)) {
                    if ($invLevelQty >= $orderItem->getQuantity() ||
                        ( $inventoryItem->isOrderOnDemandAllowed() ||
                            (
                                $inventoryItem->isCanPreorder() &&
                                $inventoryItem->getMaxQtyToPreorder() >= $orderItem->getQuantity()
                            ) ||
                            (
                                $inventoryItem->isBackorderAllowed() &&
                                $inventoryItem->getMaxQtyToBackorder() >= $orderItem->getQuantity()
                            )
                        )
                    ) {
                        $productsByWh[$warehouse->getCode()] =+1;
                    }
                }
            }
        }

        // max value is able to ship the most items from the specific warehouse
        $totalProducts = max($productsByWh);
        $whCode = array_search($totalProducts, $productsByWh);
        // only a single warehouse should be returned
        return $consolidationwhs->filter(function (Warehouse $warehouse) use ($whCode) {
            return $whCode === $warehouse->getCode();
        })->first();
    }
}
