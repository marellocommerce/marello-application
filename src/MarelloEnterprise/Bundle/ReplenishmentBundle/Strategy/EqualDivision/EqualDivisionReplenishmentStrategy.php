<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\EqualDivision;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\ReplenishmentStrategyInterface;

class EqualDivisionReplenishmentStrategy implements ReplenishmentStrategyInterface
{
    const IDENTIFIER = 'equal_division';
    const LABEL = 'marelloenterprise.replenishment.replenishment_strategies.equal_division';

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getLabel()
    {
        return self::LABEL;
    }

    /**
     * @inheritDoc
     */
    public function getResults(ReplenishmentOrderConfig $config)
    {
        $warehouseRepository = $this->manager->getRepository(Warehouse::class);
        $productRepository = $this->manager->getRepository(Product::class);

        $origins = [];
        foreach ($warehouseRepository->findBy(['id' => $config->getOrigins()]) as $origin) {
            $origins[$origin->getId()] = $origin;
        }
        $destinations = [];
        foreach ($warehouseRepository->findBy(['id' => $config->getDestinations()]) as $destination) {
            $destinations[$destination->getId()] = $destination;
        }
        $products = [];
        foreach ($productRepository->findBy(['id' => $config->getProducts()]) as $product) {
            $products[$product->getId()] = $product;
        }
        
        $originProductsQty = [];
        $destinationProductsQty = [];
        $totalQty = [];
        $result = [];
        foreach ($products as $product) {
            $totalQty[$product->getId()] = 0;
            $inventoryItems = $product->getInventoryItems();
            foreach ($inventoryItems as $inventoryItem) {
                /** @var InventoryLevel $inventoryLevel */
                foreach ($inventoryItem->getInventoryLevels() as $inventoryLevel) {
                    $warehouse =$inventoryLevel->getWarehouse();
                    if (in_array($warehouse->getId(), $config->getOrigins())) {
                        $replQty = round($inventoryLevel->getVirtualInventoryQty() * $config->getPercentage() / 100);
                        $originProductsQty[$product->getId()][$warehouse->getId()] = $replQty;
                        $totalQty[$product->getId()] += $replQty;
                    }
                }
            }
        }
        $destinationsQty = count($destinations);
        foreach ($totalQty as $product => $qty) {
            $qtyPerDestination = round($qty/$destinationsQty);
            $assignedQty = 0;
            foreach ($config->getDestinations() as $destId) {
                $assignedQty += $qtyPerDestination;
                if ($assignedQty > $qty) {
                    $extra = $assignedQty - $qty;
                    $qtyPerDestination = $qtyPerDestination - $extra;
                }
                $destinationProductsQty[$product][$destId] = $qtyPerDestination;
            }
        }
        foreach ($destinationProductsQty as $productId => $whQty) {
            foreach ($whQty as $dwh => $dqty) {
                foreach ($originProductsQty[$productId] as $owh => $oqty) {
                    if ($oqty >= $dqty) {
                        $qty = $dqty;
                        $dqty = 0;
                    } else {
                        $qty = $oqty;
                        $dqty = $dqty - $oqty;
                    }
                    if ($qty > 0) {
                        $result[] = [
                            'origin' => $origins[$owh],
                            'destination' => $destinations[$dwh],
                            'product' => $products[$productId],
                            'quantity' => $qty,
                            'total_quantity' => $oqty
                        ];
                    }
                    $originProductsQty[$productId][$owh] = $oqty - $qty;
                }
            }
        }
        
        return $result;
    }
}
