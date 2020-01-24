<?php

namespace Marello\Bundle\InventoryBundle\Twig;

use Marello\Bundle\InventoryBundle\Entity\Repository\WarehouseRepository;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;

class WarehouseExtension extends \Twig_Extension
{
    const NAME = 'marello_inventory_warehouse';
    
    /**
     * @var WarehouseRepository
     */
    protected $warehouseRepository;

    /**
     * @param WarehouseRepository $warehouseRepository
     */
    public function __construct(WarehouseRepository $warehouseRepository)
    {
        $this->warehouseRepository = $warehouseRepository;
    }

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
            new \Twig_SimpleFunction(
                'marello_inventory_get_warehouses_names_by_ids',
                [$this, 'getWarehousesNamesByIds']
            )
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
}
