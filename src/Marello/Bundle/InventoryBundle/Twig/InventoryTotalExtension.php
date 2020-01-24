<?php

namespace Marello\Bundle\InventoryBundle\Twig;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryTotalCalculator;

class InventoryTotalExtension extends \Twig_Extension
{
    const NAME = 'marello_inventory_inventorylevel_total';
    
    /** @var InventoryTotalCalculator $totalsCalculator */
    protected $totalsCalculator;

    /**
     * InventoryTotalExtension constructor.
     *
     * @param InventoryTotalCalculator $totalsCalculator
     */
    public function __construct(InventoryTotalCalculator $totalsCalculator)
    {
        $this->totalsCalculator = $totalsCalculator;
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
                'marello_inventory_get_inventorylevel_total_inventory',
                [$this, 'getTotalInventory']
            ),
            new \Twig_SimpleFunction(
                'marello_inventory_get_inventorylevel_total_allocatedinventory',
                [$this, 'getTotalAllocatedInventory']
            ),
            new \Twig_SimpleFunction(
                'marello_inventory_get_inventorylevel_total_virtualinventory',
                [$this, 'getTotalVirtualInventory']
            )
        ];
    }

    /**
     * {@inheritdoc}
     * @param InventoryItem $inventoryItem
     * @return int
     */
    public function getTotalInventory(InventoryItem $inventoryItem)
    {
        return $this->totalsCalculator->getTotalInventoryQty($inventoryItem);
    }

    /**
     * {@inheritdoc}
     * @param InventoryItem $inventoryItem
     * @return int
     */
    public function getTotalAllocatedInventory(InventoryItem $inventoryItem)
    {
        return $this->totalsCalculator->getTotalAllocatedInventoryQty($inventoryItem);
    }

    /**
     * {@inheritdoc}
     * @param InventoryItem $inventoryItem
     * @return int
     */
    public function getTotalVirtualInventory(InventoryItem $inventoryItem)
    {
        return $this->totalsCalculator->getTotalVirtualInventoryQty($inventoryItem);
    }
}
