<?php

namespace Marello\Bundle\PaymentTermBundle\Controller;

use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PaymentTermController extends AbstractController
{
    /**
     * @return array
     *
     * @Route(
     *     path="/", 
     *     name="marello_paymentterm_paymentterm_index"
     * )
     * @Template
     */
    public function indexAction()
    {
        return [
            'entityClass' => PaymentTerm::class,
        ];
    }

    /**
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route(
     *     path="/create", 
     *     name="marello_paymentterm_paymentterm_create"
     * )
     * @Template("MarelloPaymentTermBundle:PaymentTerm:update.html.twig")
     */
    public function createAction()
    {
        $entity = new PaymentTerm();

        return $this->update($entity);
    }

    /**
     * @param PaymentTerm $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route(
     *     path="/update/{id}", 
     *     requirements={"id" = "\d+"}, 
     *     name="marello_paymentterm_paymentterm_update"
     * )
     * @Template("MarelloPaymentTermBundle:PaymentTerm:update.html.twig")
     */
    public function updateAction(PaymentTerm $entity)
    {
        return $this->update($entity);
    }

    /**
     * @param PaymentTerm $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function update(PaymentTerm $entity)
    {
        $handler = $this->get('marello_payment_term.payment_term.form.handler');

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

    /**
     * @param PaymentTerm $entity
     * @return array
     *
     * @Route(
     *     path="/view/{id}", 
     *     requirements={"id" = "\d+"}, 
     *     name="marello_paymentterm_paymentterm_view"
     * )
     * @Template
     */
    public function viewAction(PaymentTerm $entity)
    {
        return [
            'entity' => $entity,
        ];
    }
}
