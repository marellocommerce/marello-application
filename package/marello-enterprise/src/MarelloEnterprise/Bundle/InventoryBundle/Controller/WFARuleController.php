<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WFARuleType;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class WFARuleController extends Controller
{
    /**
     * @Route("/", name="marelloenterprise_inventory_wfa_rule_index")
     * @Template
     * @AclAncestor("marelloenterprise_inventory_wfa_rule_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => WFARule::class
        ];
    }

    /**
     * @Route("/create", name="marelloenterprise_inventory_wfa_rule_create")
     * @Template("MarelloEnterpriseInventoryBundle:WFARule:update.html.twig")
     * @Acl(
     *     id="marelloenterprise_inventory_wfa_rule_create",
     *     type="entity",
     *     permission="CREATE",
     *     class="MarelloEnterpriseInventoryBundle:WFARule"
     * )
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new WFARule(), $request);
    }

    /**
     * @Route("/view/{id}", name="marelloenterprise_inventory_wfa_rule_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="marelloenterprise_inventory_wfa_rule_view",
     *      type="entity",
     *      class="MarelloEnterpriseInventoryBundle:WFARule",
     *      permission="VIEW"
     * )
     *
     * @param WFARule $wfaRule
     *
     * @return array
     */
    public function viewAction(WFARule $wfaRule)
    {
        return [
            'entity' => $wfaRule,
        ];
    }

    /**
     * @Route("/update/{id}", name="marelloenterprise_inventory_wfa_rule_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *     id="marelloenterprise_inventory_wfa_rule_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="MarelloEnterpriseInventoryBundle:WFARule"
     * )
     * @param Request $request
     * @param WFARule $entity
     *
     * @return array
     */
    public function updateAction(Request $request, WFARule $entity)
    {
        if ($entity->getRule()->isSystem()) {
            $this->addFlash(
                'warning',
                'marelloenterprise.inventory.messages.warning.wfarule.is_system_update_attempt'
            );

            return $this->redirect($this->generateUrl('marelloenterprise_inventory_wfa_rule_index'));
        }

        return $this->update($entity, $request);
    }

    /**
     * @param WFARule $entity
     * @param Request $request
     * @return array|RedirectResponse
     */
    protected function update(WFARule $entity, Request $request)
    {
        return $this->get('oro_form.update_handler')->update(
            $entity,
            $this->createForm(WFARuleType::class, $entity),
            $this->get('translator')->trans('marelloenterprise.inventory.messages.success.wfarule.saved'),
            $request
        );
    }
}
