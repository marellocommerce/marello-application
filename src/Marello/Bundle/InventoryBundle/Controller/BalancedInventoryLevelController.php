<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Async\Topics;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BalancedInventoryLevelController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_inventory_balancedinventorylevel_index"
     * )
     * @AclAncestor("marello_inventory_inventory_view")
     * @Template("@MarelloInventory/BalancedInventoryLevel/index.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => BalancedInventoryLevel::class,
        ];
    }

    /**
     * @Route(
     *     path="/recalculate",
     *     name="marello_inventory_balancedinventorylevel_recalculate"
     * )
     * @Acl(
     *      id="marello_inventory_inventory_recalculate_update",
     *      type="entity",
     *      class="MarelloInventoryBundle:BalancedInventoryLevel",
     *      permission="EDIT"
     * )
     */
    public function recalculateAction()
    {
        $messageProducer = $this->container->get('oro_message_queue.client.message_producer');
        $messageProducer->send(
            Topics::RESOLVE_REBALANCE_ALL_INVENTORY,
            Topics::ALL_INVENTORY
        );

        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans('marello.inventory.messages.success.inventory_rebalance.started')
        );
        return $this->redirectToRoute('marello_inventory_balancedinventorylevel_index');
    }
}
