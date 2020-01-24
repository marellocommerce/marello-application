<?php

namespace Marello\Bundle\PaymentTermBundle\Controller;

use Marello\Bundle\PaymentTermBundle\Entity\PaymentTerm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentTermController extends Controller
{
    /**
     * @param Request $request
     * @return array
     *
     * @Route("/", name="marello_paymentterm_paymentterm_index")
     * @Template
     */
    public function indexAction(Request $request)
    {
        return [
            'entityClass' => PaymentTerm::class,
        ];
    }

    /**
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/create", name="marello_paymentterm_paymentterm_create")
     * @Template("MarelloPaymentTermBundle:PaymentTerm:update.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new PaymentTerm();

        return $this->update($entity);
    }

    /**
     * @param Request $request
     * @param PaymentTerm $entity
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/update/{id}", requirements={"id" = "\d+"}, name="marello_paymentterm_paymentterm_update")
     * @Template("MarelloPaymentTermBundle:PaymentTerm:update.html.twig")
     */
    public function updateAction(Request $request, PaymentTerm $entity)
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
     * @param Request $request
     * @param PaymentTerm $entity
     * @return array
     *
     * @Route("/view/{id}", requirements={"id" = "\d+"}, name="marello_paymentterm_paymentterm_view")
     * @Template
     */
    public function viewAction(Request $request, PaymentTerm $entity)
    {
        return [
            'entity' => $entity,
        ];
    }
}
