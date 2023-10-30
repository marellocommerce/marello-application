<?php

namespace Marello\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Async\Topic\ResolveRebalanceAllInventoryTopic;
use Marello\Bundle\InventoryBundle\Entity\BalancedInventoryLevel;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @param Request $request
     */
    public function recalculateAction(Request $request)
    {
        $messageProducer = $this->container->get(MessageProducerInterface::class);
        $messageProducer->send(
            ResolveRebalanceAllInventoryTopic::getName(),
            []
        );

        $request->getSession()->getFlashBag()->add(
            'success',
            $this->container
                ->get(TranslatorInterface::class)
                ->trans('marello.inventory.messages.success.inventory_rebalance.started')
        );
        return $this->redirectToRoute('marello_inventory_balancedinventorylevel_index');
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                MessageProducerInterface::class,
                TranslatorInterface::class,
            ]
        );
    }
}
