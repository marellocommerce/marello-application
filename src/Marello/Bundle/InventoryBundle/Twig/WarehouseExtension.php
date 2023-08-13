<?php

namespace Marello\Bundle\InventoryBundle\Twig;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WarehouseExtension extends AbstractExtension
{
    const NAME = 'marello_inventory_warehouse';
    
    /**
     * @var WarehouseRepository
     */
    protected $warehouseRepository;

    public function __construct(
        private ManagerRegistry $registry,
        private InventoryManager $inventoryManager
    ) {}

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_inventory_get_warehouses_names_by_ids',
                [$this, 'getWarehousesNamesByIds']
            ),
            new TwigFunction(
                'get_expected_inventory_total',
                [$this, 'getExpectedInventoryTotal']
            ),
            new TwigFunction(
                'get_expired_sell_by_date_total',
                [$this, 'getExpiredSellByDateTotal']
            ),
        ];
    }

    /**
     * @param array $warehousesIds
     * @return int
     */
    public function getWarehousesNamesByIds(array $warehousesIds)
    {
        $warehouses = $this->warehouseRepository->findBy(['id' => $warehousesIds]);
        $warehousesNames = implode(', ', array_map(function (Warehouse $warehouse) {
            return $warehouse->getLabel();
        }, $warehouses));

        return $warehousesNames;
    }

    public function getExpectedInventoryTotal($entity)
    {
        return $this->inventoryManager->getExpectedInventoryTotal($entity);
    }

    public function getExpiredSellByDateTotal(InventoryItem $entity)
    {
        return $this->inventoryManager->getExpiredSellByDateTotal($entity);
    }

    protected function getRepository(): WarehouseRepository
    {
        if (!$this->warehouseRepository) {
            $this->warehouseRepository = $this->registry->getRepository(Warehouse::class);
        }

        return $this->warehouseRepository;
    }
}
