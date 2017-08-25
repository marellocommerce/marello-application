<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Controller\InventoryLevelController as BaseController;
/**
 * @Config\Route("/inventory-level")
 */
class InventoryLevelController extends BaseController
{
    /**
     * @Config\Route("/{id}", requirements={"id"="\d+"}, name="marello_inventory_inventorylevel_index")
     * @Config\Template
     *
     * @param InventoryItem $inventoryItem
     *
     * @return array
     */
    public function indexAction(InventoryItem $inventoryItem)
    {
        return parent::indexAction($inventoryItem);
    }

    /**
     * @Config\Route("/chart/{id}", requirements={"id"="\d+"}, name="marello_inventory_inventorylevel_chart")
     * @Config\Template
     *
     * @param InventoryItem $inventoryItem
     * @param Request $request
     *
     * @return array
     */
    public function chartAction(InventoryItem $inventoryItem, Request $request)
    {
        return parent::chartAction($inventoryItem, $request);
    }
}
