<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Oro\Bundle\SecurityBundle\Annotation as Security;

use Marello\Bundle\InventoryBundle\Async\Topics;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;

/**
 * @Config\Route("/virtual-inventory-level")
 */
class VirtualInventoryLevelController extends Controller
{
    /**
     * @Config\Route("/", name="marello_inventory_virtualinventorylevel_index")
     * @Security\AclAncestor("marello_inventory_inventory_view")
     * @Config\Template
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => VirtualInventoryLevel::class,
        ];
    }

    /**
     * @Config\Route("/recalculate", name="marello_inventory_virtualinventorylevel_recalculate")
     * @Security\Acl(
     *      id="marello_inventory_inventory_recalculate_update",
     *      type="entity",
     *      class="MarelloInventoryBundle:VirtualInventoryLevel",
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
        return $this->redirectToRoute('marello_inventory_virtualinventorylevel_index');
    }
}
