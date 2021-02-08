<?php

namespace Marello\Bundle\PaymentBundle\Controller;

use Marello\Bundle\PaymentBundle\Entity\Payment;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    /**
     * @Route(path="/", name="marello_payment_index")
     * @Template("MarelloPaymentBundle:Payment:index.html.twig")
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
     * @Template("MarelloPaymentBundle:Payment:view.html.twig")
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
     * @Template("MarelloPaymentBundle:Payment:create.html.twig")
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
     * @Template("MarelloPaymentBundle:Payment:update.html.twig")
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
            $handler = $this->get('marello_payment.form.handler.payment_create');
        } else {
            $handler = $this->get('marello_payment.form.handler.payment_update');
        }

        if ($handler->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.payment_term.ui.payment_term.saved.message')
            );

            return $this->get('oro_ui.router')->redirect($entity);
        }

        return [
            'entity' => $entity,
            'form'   => $handler->getFormView(),
        ];
    }
}
