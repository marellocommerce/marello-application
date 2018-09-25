<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

/**
 * Class InventoryController
 * @deprecated since version 1.3, will be removed in 1.4
 * @package Marello\Bundle\InventoryBundle\Controller
 */
class InventoryController extends InventoryItemController
{
    /**
     * @return array
     */
    public function indexAction()
    {
        return parent::indexAction();
    }

    /**
     * @param InventoryItem $inventoryItem
     *
     * @return array
     */
    public function viewAction(InventoryItem $inventoryItem)
    {
        return parent::viewAction($inventoryItem);
    }

    /**
     * @param InventoryItem $inventoryItem
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function updateAction(InventoryItem $inventoryItem, Request $request)
    {
        return parent::updateAction($inventoryItem, $request);
    }

    /**
     * @param InventoryItem $inventoryItem
     *
     * @return array
     */
    public function infoAction(InventoryItem $inventoryItem)
    {
        return parent::infoAction($inventoryItem);
    }

    /**
      * @param InventoryItem $inventoryItem
     *
     * @return array
     */
    public function datagridAction(InventoryItem $inventoryItem)
    {
        return parent::datagridAction($inventoryItem);
    }
}
