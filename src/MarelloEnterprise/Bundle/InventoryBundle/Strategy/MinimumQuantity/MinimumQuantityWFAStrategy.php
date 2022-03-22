<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseChannelGroupLinkRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ProductBundle\Entity\Product;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\MinimumQuantity\Calculator\MinQtyWHCalculatorInterface;
use MarelloEnterprise\Bundle\InventoryBundle\Strategy\WFAStrategyInterface;

class MinimumQuantityWFAStrategy implements WFAStrategyInterface
{
    const IDENTIFIER = 'min_quantity';
    const LABEL = 'marelloenterprise.inventory.strategies.min_quantity';

    /**
     * @var bool
     */
    private $estimation = false;

    /**
     * @var WarehouseChannelGroupLinkRepository
     */
    private $warehouseChannelGroupLinkRepository;

    /**
     * @var Warehouse[]
     */
    private $linkedWarehouses = [];

    public function __construct(
        private MinQtyWHCalculatorInterface $minQtyWHCalculator,
        private ManagerRegistry $registry
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return self::LABEL;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setEstimation($estimation = false)
    {
        $this->estimation = $estimation;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehouseResults(Order $order, array $initialResults = [])
    {
        $productsByWh = [];
        $warehouses = [];
        $orderItems = $order->getItems();
        $orderItemsByProducts = [];

        $linkedWarehouses = $this->getLinkedWarehouses($order);

        if (empty($linkedWarehouses)) {
            return [];
        }
        $warehousesIds = array_map(function (Warehouse $warehouse) {
            return $warehouse->getId();
        }, $linkedWarehouses);

        foreach ($orderItems as $key => $orderItem) {
            $orderItemsByProducts[sprintf(
                '%s_|_%s',
                $orderItem->getProduct()->getSku(),
                $orderItem->getId() ? : $key
            )] = $orderItem;
            $inventoryItems = $orderItem->getInventoryItems();
            foreach ($inventoryItems as $inventoryItem) {
                /** @var InventoryLevel $inventoryLevel */
                foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                    $warehouse = $inventoryLevel->getWarehouse();
                    $warehouseType = $warehouse->getWarehouseType()->getName();
                    $warehouseId = $warehouse->getId();
                    if ((
                            $inventoryLevel->getInventoryQty() >= $orderItem->getQuantity() ||
                            $warehouseType === WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL ||
                            ( $this->estimation === true &&
                                (
                                    $inventoryItem->isOrderOnDemandAllowed() ||
                                    (
                                        $inventoryItem->isCanPreorder() &&
                                        $inventoryItem->getMaxQtyToPreorder() >= $orderItem->getQuantity()
                                    ) ||
                                    (
                                        $inventoryItem->isBackorderAllowed() &&
                                        $inventoryItem->getMaxQtyToBackorder() >= $orderItem->getQuantity()
                                    )
                                )
                            )
                        ) && in_array($warehouseId, $warehousesIds)
                    ) {
                        $warehouses[$warehouseId] = $warehouse;
                        $productsByWh[$warehouseId] [] = $inventoryItem->getProduct()->getSku();
                    }
                }
            }
        }

        uasort($productsByWh, function ($a, $b) {
            return count($b) > count($a) ? 1 : -1 ;
        });

        return $this->minQtyWHCalculator->calculate($productsByWh, $orderItemsByProducts, $warehouses, $orderItems);
    }

    /**
     * @param Product $product
     * @return Warehouse|null
     */
    protected function getPreferredExternalWarehouse(Product $product)
    {
        $preferredSupplier = null;
        $preferredPriority = 0;
        foreach ($product->getSuppliers() as $productSupplierRelation) {
            if (null == $preferredSupplier && $productSupplierRelation->getCanDropship() === true) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
                continue;
            }
            if ($productSupplierRelation->getPriority() < $preferredPriority  &&
                $productSupplierRelation->getCanDropship() === true) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
            }
        }

        return $preferredSupplier;
    }

    /**
     * @param Order $order
     * @return Warehouse[]
     */
    private function getLinkedWarehouses(Order $order)
    {
        if (empty($this->linkedWarehouses)) {
            if (!$order->getSalesChannel() || !$order->getSalesChannel()->getGroup()) {
                return [];
            }
            /** @var WarehouseChannelGroupLink $warehouseGroupLink */
            $warehouseGroupLink = $this->getRepository()
                ->findLinkBySalesChannelGroup($order->getSalesChannel()->getGroup());

            if (!$warehouseGroupLink) {
                return [];
            }

            /** @var Warehouse[] $linkedWarehouses */
            $linkedWarehouses = $warehouseGroupLink
                ->getWarehouseGroup()
                ->getWarehouses()
                ->toArray();

            $this->linkedWarehouses = $linkedWarehouses;
        }

        return $this->linkedWarehouses;
    }

    protected function getRepository(): WarehouseChannelGroupLinkRepository
    {
        if (!$this->warehouseChannelGroupLinkRepository) {
            $this->warehouseChannelGroupLinkRepository = $this->registry
                ->getRepository(WarehouseChannelGroupLink::class);
        }

        return $this->warehouseChannelGroupLinkRepository;
    }
}
