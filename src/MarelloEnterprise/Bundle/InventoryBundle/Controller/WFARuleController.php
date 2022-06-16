<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Controller;

use MarelloEnterprise\Bundle\InventoryBundle\Entity\WFARule;
use MarelloEnterprise\Bundle\InventoryBundle\Form\Type\WFARuleType;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class WFARuleController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marelloenterprise_inventory_wfa_rule_index"
     * )
     * @Template("@MarelloEnterpriseInventory/WFARule/index.html.twig")
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
     * @Route(
     *     path="/create",
     *     name="marelloenterprise_inventory_wfa_rule_create"
     * )
     * @Template("@MarelloEnterpriseInventory/WFARule/update.html.twig")
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
     * @Route(
     *     path="/view/{id}",
     *     name="marelloenterprise_inventory_wfa_rule_view",
     *     requirements={"id"="\d+"}
     * )
     * @Template("@MarelloEnterpriseInventory/WFARule/view.html.twig")
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
     * @Route(
     *     path="/update/{id}",
     *     name="marelloenterprise_inventory_wfa_rule_update",
     *     requirements={"id"="\d+"}
     * )
     * @Template("@MarelloEnterpriseInventory/WFARule/update.html.twig")
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
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->createForm(WFARuleType::class, $entity),
            $this->container
                ->get(TranslatorInterface::class)
                ->trans('marelloenterprise.inventory.messages.success.wfarule.saved'),
            $request
        );
    }

    /**
     * @Route(
     *     path="/{gridName}/massAction/{actionName}",
     *     name="marelloenterprise_inventory_wfa_rule_massaction"
     * )
     * @Acl(
     *     id="marelloenterprise_inventory_wfa_rule_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="MarelloEnterpriseInventoryBundle:WFARule"
     * )
     * @param string $gridName
     * @param string $actionName
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function markMassAction($gridName, $actionName, Request $request)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->container->get(MassActionDispatcher::class);

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $request);

        $data = [
            'successful' => $response->isSuccessful(),
            'message' => $response->getMessage()
        ];

        return new JsonResponse(array_merge($data, $response->getOptions()));
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                UpdateHandlerFacade::class,
                TranslatorInterface::class,
                MassActionDispatcher::class,
            ]
        );
    }
}
