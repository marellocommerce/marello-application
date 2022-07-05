<?php

namespace Marello\Bundle\PaymentBundle\Controller;

use Marello\Bundle\PaymentBundle\Entity\Payment;
use Marello\Bundle\PaymentBundle\Form\Handler\PaymentCreateHandler;
use Marello\Bundle\PaymentBundle\Form\Handler\PaymentUpdateHandler;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentController extends AbstractController
{
    /**
     * @Route(path="/", name="marello_payment_index")
     * @Template("@MarelloPayment/Payment/index.html.twig")
     * @AclAncestor("marello_payment_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => Payment::class
        ];
    }

    /**
     * @Route(path="/view/{id}", name="marello_payment_view", requirements={"id"="\d+"})
     * @Template("@MarelloPayment/Payment/view.html.twig")
     * @Acl(
     *      id="marello_payment_view",
     *      type="entity",
     *      class="MarelloPaymentBundle:Payment",
     *      permission="VIEW"
     * )
     *
     * @param Payment $payment
     *
     * @return array
     */
    public function viewAction(Payment $payment)
    {
        return [
            'entity' => $payment,
        ];
    }

    /**
     * @Route(path="/create", name="marello_payment_create")
     * @Template("@MarelloPayment/Payment/create.html.twig")
     * @Acl(
     *     id="marello_payment_create",
     *     type="entity",
     *     permission="CREATE",
     *     class="MarelloPaymentBundle:Payment"
     * )
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update($request);
    }

    /**
     * @Route(path="/update/{id}", name="marello_payment_update", requirements={"id"="\d+"})
     * @Template("@MarelloPayment/Payment/update.html.twig")
     * @Acl(
     *     id="marello_payment_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="MarelloPaymentBundle:Payment"
     * )
     * @param Request $request
     * @param Payment $entity
     *
     * @return array
     */
    public function updateAction(Request $request, Payment $entity)
    {
        return $this->update($request, $entity);
    }

    /**
     * @param Request $request
     * @param Payment|null $entity
     * @return array|RedirectResponse
     */
    protected function update(Request $request, Payment $entity = null)
    {
        if ($entity === null) {
            $entity = new Payment();
            $handler = $this->container->get(PaymentCreateHandler::class);
        } else {
            $handler = $this->container->get(PaymentUpdateHandler::class);
        }

        if ($handler->process($entity)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.payment_term.ui.payment_term.saved.message')
            );

            return $this->container->get(Router::class)->redirect($entity);
        }

        return [
            'entity' => $entity,
            'form'   => $handler->getFormView(),
        ];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                PaymentCreateHandler::class,
                PaymentUpdateHandler::class,
                TranslatorInterface::class,
                Router::class,
            ]
        );
    }
}
