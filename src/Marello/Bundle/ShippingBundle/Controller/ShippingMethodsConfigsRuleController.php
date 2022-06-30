<?php

namespace Marello\Bundle\ShippingBundle\Controller;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Marello\Bundle\ShippingBundle\Form\Handler\ShippingMethodsConfigsRuleHandler;
use Marello\Bundle\ShippingBundle\Form\Type\ShippingMethodsConfigsRuleType;
use Oro\Bundle\UIBundle\Route\Router;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShippingMethodsConfigsRuleController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_shipping_methods_configs_rule_index"
     * )
     * @Template
     * @AclAncestor("marello_shipping_methods_configs_rule_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => ShippingMethodsConfigsRule::class
        ];
    }

    /**
     * @Route(
     *     path="/create",
     *     name="marello_shipping_methods_configs_rule_create"
     * )
     * @Template("@MarelloShipping/ShippingMethodsConfigsRule/update.html.twig")
     * @Acl(
     *     id="marello_shipping_methods_configs_rule_create",
     *     type="entity",
     *     permission="CREATE",
     *     class="MarelloShippingBundle:ShippingMethodsConfigsRule"
     * )
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new ShippingMethodsConfigsRule(), $request);
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     name="marello_shipping_methods_configs_rule_view",
     *     requirements={"id"="\d+"}
     * )
     * @Template
     * @Acl(
     *      id="marello_shipping_methods_configs_rule_view",
     *      type="entity",
     *      class="MarelloShippingBundle:ShippingMethodsConfigsRule",
     *      permission="VIEW"
     * )
     *
     * @param ShippingMethodsConfigsRule $shippingRule
     *
     * @return array
     */
    public function viewAction(ShippingMethodsConfigsRule $shippingRule)
    {
        return [
            'entity' => $shippingRule,
        ];
    }

    /**
     * @Route(
     *     path="/update/{id}",
     *     name="marello_shipping_methods_configs_rule_update",
     *     requirements={"id"="\d+"}
     * )
     * @Template
     * @Acl(
     *     id="marello_shipping_methods_configs_rule_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="MarelloShippingBundle:ShippingMethodsConfigsRule"
     * )
     * @param Request $request
     * @param ShippingMethodsConfigsRule $entity
     *
     * @return array
     */
    public function updateAction(Request $request, ShippingMethodsConfigsRule $entity)
    {
        return $this->update($entity, $request);
    }

    /**
     * @param ShippingMethodsConfigsRule $entity
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function update(ShippingMethodsConfigsRule $entity, Request $request)
    {
        $form = $this->createForm(ShippingMethodsConfigsRuleType::class);
        if ($this->container->get(ShippingMethodsConfigsRuleHandler::class)->process($form, $entity)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.shipping.controller.rule.saved.message')
            );

            return $this->container->get(Router::class)->redirect($entity);
        }

        if ($request->get(ShippingMethodsConfigsRuleHandler::UPDATE_FLAG, false)) {
            // take different form due to JS validation should be shown even in case
            // when it was not validated on backend
            $form = $this->createForm(ShippingMethodsConfigsRuleType::class, $form->getData());
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView()
        ];
    }

    /**
     * @Route(
     *     path="/{gridName}/massAction/{actionName}",
     *     name="marello_status_shipping_rule_massaction"
     * )
     * @Acl(
     *     id="marello_shipping_methods_configs_rule_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="MarelloShippingBundle:ShippingMethodsConfigsRule"
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
                ShippingMethodsConfigsRuleHandler::class,
                TranslatorInterface::class,
                Router::class,
                MassActionDispatcher::class,
            ]
        );
    }
}
