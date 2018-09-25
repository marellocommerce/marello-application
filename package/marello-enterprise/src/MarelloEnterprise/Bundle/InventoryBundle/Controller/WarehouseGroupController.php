<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use Marello\Bundle\InventoryBundle\Entity\WarehouseGroup;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WarehouseGroupType;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class WarehouseGroupController extends Controller
{
    /**
     * @Route("/", name="marelloenterprise_inventory_warehousegroup_index")
     * @Template
     * @AclAncestor("marelloenterprise_inventory_warehousegroup_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => WarehouseGroup::class
        ];
    }

    /**
     * @Route("/create", name="marelloenterprise_inventory_warehousegroup_create")
     * @Template("MarelloEnterpriseInventoryBundle:WarehouseGroup:update.html.twig")
     * @Acl(
     *     id="marelloenterprise_inventory_warehousegroup_create",
     *     type="entity",
     *     permission="CREATE",
     *     class="MarelloInventoryBundle:WarehouseGroup"
     * )
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new WarehouseGroup(), $request);
    }

    /**
     * @Route("/view/{id}", name="marelloenterprise_inventory_warehousegroup_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="marelloenterprise_inventory_warehousegroup_view",
     *      type="entity",
     *      class="MarelloInventoryBundle:WarehouseGroup",
     *      permission="VIEW"
     * )
     *
     * @param WarehouseGroup $warehouseGroup
     *
     * @return array
     */
    public function viewAction(WarehouseGroup $warehouseGroup)
    {
        return [
            'entity' => $warehouseGroup,
        ];
    }

    /**
     * @Route("/update/{id}", name="marelloenterprise_inventory_warehousegroup_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *     id="marelloenterprise_inventory_warehousegroup_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="MarelloInventoryBundle:WarehouseGroup"
     * )
     * @param Request $request
     * @param WarehouseGroup $entity
     *
     * @return array
     */
    public function updateAction(Request $request, WarehouseGroup $entity)
    {
        if ($entity->isSystem()) {
            $this->addFlash(
                'warning',
                'marello.sales.messages.warning.wfarule.is_system_update_attempt'
            );

            return $this->redirect($this->generateUrl('marelloenterprise_inventory_warehousegroup_index'));
        }

        return $this->update($entity, $request);
    }

    /**
     * @param WarehouseGroup $entity
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function update(WarehouseGroup $entity, Request $request)
    {
        return $this->get('oro_form.update_handler')->update(
            $entity,
            $this->createForm(WarehouseGroupType::class, $entity),
            $this->get('translator')->trans('marelloenterprise.inventory.messages.success.warehousegroup.saved'),
            $request,
            'marelloenterprise_inventory.form_handler.warehousegroup'
        );
    }
}
