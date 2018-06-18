<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseChannelGroupLinkType;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class WarehouseChannelGroupLinkController extends Controller
{
    /**
     * @Route("/", name="marelloenterprise_inventory_warehousechannelgrouplink_index")
     * @Template
     * @Acl(
     *      id="marelloenterprise_inventory_warehousechannelgrouplink_view",
     *      type="entity",
     *      class="MarelloInventoryBundle:WarehouseChannelGroupLink",
     *      permission="VIEW"
     * )
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => WarehouseChannelGroupLink::class
        ];
    }

    /**
     * @Route("/create", name="marelloenterprise_inventory_warehousechannelgrouplink_create")
     * @Template("MarelloEnterpriseInventoryBundle:WarehouseChannelGroupLink:update.html.twig")
     * @Acl(
     *     id="marelloenterprise_inventory_warehousechannelgrouplink_create",
     *     type="entity",
     *     permission="CREATE",
     *     class="MarelloInventoryBundle:WarehouseChannelGroupLink"
     * )
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new WarehouseChannelGroupLink(), $request);
    }

    /**
     * @Route("/update/{id}", name="marelloenterprise_inventory_warehousechannelgrouplink_update",
     *     requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *     id="marelloenterprise_inventory_warehousechannelgrouplink_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="MarelloInventoryBundle:WarehouseChannelGroupLink"
     * )
     * @param Request $request
     * @param WarehouseChannelGroupLink $entity
     *
     * @return array
     */
    public function updateAction(Request $request, WarehouseChannelGroupLink $entity)
    {
        if ($entity->isSystem()) {
            $this->addFlash(
                'warning',
                'marello.sales.messages.warning.wfarule.is_system_update_attempt'
            );

            return $this->redirect($this->generateUrl('marelloenterprise_inventory_warehousechannelgrouplink_index'));
        }

        return $this->update($entity, $request);
    }

    /**
     * @param WarehouseChannelGroupLink $entity
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function update(WarehouseChannelGroupLink $entity, Request $request)
    {
        return $this->get('oro_form.update_handler')->update(
            $entity,
            $this->createForm(WarehouseChannelGroupLinkType::class, $entity),
            $this
                ->get('translator')
                ->trans('marelloenterprise.inventory.messages.success.warehousechannelgrouplink.saved'),
            $request
        );
    }
}
