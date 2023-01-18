<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Symfony\Component\Routing\Annotation\Route;

class ExpectedInventoryItemController extends AbstractController
{
    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marelloenterprise_inventory_expected_inventory_item_view"
     * )
     * @Template("@MarelloEnterpriseInventory/ExpectedInventoryItem/view.html.twig")
     * @Acl(
     *      id="marello_inventory_inventory_view",
     *      type="entity",
     *      class="MarelloInventoryBundle:InventoryItem",
     *      permission="VIEW"
     * )
     * @param InventoryItem $inventoryItem
     *
     * @return array
     */
    public function viewAction(InventoryItem $inventoryItem)
    {
        return [
            'entity' => $inventoryItem,
        ];
    }
}
