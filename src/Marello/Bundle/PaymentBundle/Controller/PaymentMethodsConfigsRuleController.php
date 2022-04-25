<?php

namespace Marello\Bundle\PaymentBundle\Controller;

use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Marello\Bundle\PaymentBundle\Form\Handler\PaymentMethodsConfigsRuleHandler;
use Marello\Bundle\PaymentBundle\Form\Type\PaymentMethodsConfigsRuleType;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Payment Methods Configs Rule Controller
 */
class PaymentMethodsConfigsRuleController extends AbstractController
{
    /**
     * @Route(path="/", name="marello_payment_methods_configs_rule_index")
     * @Template("@MarelloPayment/PaymentMethodsConfigsRule/index.html.twig")
     * @AclAncestor("marello_payment_methods_configs_rule_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => PaymentMethodsConfigsRule::class
        ];
    }

    /**
     * @Route(path="/create", name="marello_payment_methods_configs_rule_create")
     * @Template("@MarelloPayment/PaymentMethodsConfigsRule/update.html.twig")
     * @Acl(
     *     id="marello_payment_methods_configs_rule_create",
     *     type="entity",
     *     permission="CREATE",
     *     class="MarelloPaymentBundle:PaymentMethodsConfigsRule"
     * )
     *
     * @param Request $request
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update(new PaymentMethodsConfigsRule(), $request);
    }

    /**
     * @Route(path="/view/{id}", name="marello_payment_methods_configs_rule_view", requirements={"id"="\d+"})
     * @Template("@MarelloPayment/PaymentMethodsConfigsRule/view.html.twig")
     * @Acl(
     *      id="marello_payment_methods_configs_rule_view",
     *      type="entity",
     *      class="MarelloPaymentBundle:PaymentMethodsConfigsRule",
     *      permission="VIEW"
     * )
     *
     * @param PaymentMethodsConfigsRule $paymentMethodsConfigsRule
     *
     * @return array
     */
    public function viewAction(PaymentMethodsConfigsRule $paymentMethodsConfigsRule)
    {
        return [
            'entity' => $paymentMethodsConfigsRule,
        ];
    }

    /**
     * @Route(path="/update/{id}", name="marello_payment_methods_configs_rule_update", requirements={"id"="\d+"})
     * @Template("@MarelloPayment/PaymentMethodsConfigsRule/update.html.twig")
     * @Acl(
     *     id="marello_payment_methods_configs_rule_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="MarelloPaymentBundle:PaymentMethodsConfigsRule"
     * )
     * @param Request $request
     * @param PaymentMethodsConfigsRule $entity
     *
     * @return array
     */
    public function updateAction(Request $request, PaymentMethodsConfigsRule $entity)
    {
        return $this->update($entity, $request);
    }

    /**
     * @param PaymentMethodsConfigsRule $entity
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function update(PaymentMethodsConfigsRule $entity, Request $request)
    {
        $form = $this->createForm(PaymentMethodsConfigsRuleType::class);
        $queryParams = $request->query->all();
        if ($this->container->get(PaymentMethodsConfigsRuleHandler::class)->process($form, $entity)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.payment.controller.rule.saved.message')
            );

            return $this->container->get(Router::class)->redirect($entity);
        }

        if ($request->get(PaymentMethodsConfigsRuleHandler::UPDATE_FLAG, false)) {
            // take different form due to JS validation should be shown even in case
            // when it was not validated on backend
            $form = $this->createForm(PaymentMethodsConfigsRuleType::class, $form->getData());
        }

        return [
            'entity' => $entity,
            'queryParams' => $queryParams,
            'form'   => $form->createView()
        ];
    }

    /**
     * @Route(path="/{gridName}/massAction/{actionName}", name="marello_payment_methods_configs_massaction")
     * @Acl(
     *     id="marello_payment_methods_configs_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="MarelloPaymentBundle:PaymentMethodsConfigsRule"
     * )
     * @CsrfProtection()
     *
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
                PaymentMethodsConfigsRuleHandler::class,
                TranslatorInterface::class,
                Router::class,
                MassActionDispatcher::class,
            ]
        );
    }
}
