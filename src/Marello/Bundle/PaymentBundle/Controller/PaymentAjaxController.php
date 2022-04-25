<?php

namespace Marello\Bundle\PaymentBundle\Controller;

use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\LayoutBundle\Provider\CompositeFormChangesProvider;
use Marello\Bundle\PaymentBundle\Entity\Payment;
use Marello\Bundle\PaymentBundle\Form\Type\PaymentCreateType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentAjaxController extends AbstractController
{
    /**
     * @Route(
     *     path="/form-changes/{id}",
     *     methods={"POST"},
     *     name="marello_payment_form_changes",
     *     defaults={"id" = 0}
     * )
     * @AclAncestor("marello_payment_create")
     *
     * @param Request $request
     * @param Payment|null $payment
     * @return JsonResponse
     */
    public function formChangesAction(Request $request, Payment $payment = null)
    {
        if (!$payment) {
            $payment = new Payment();
        }

        $form = $this->getType($payment);
        $submittedData = $request->get($form->getName());

        $form->submit($submittedData);

        $context = new FormChangeContext(
            [
                FormChangeContext::FORM_FIELD => $form,
                FormChangeContext::SUBMITTED_DATA_FIELD => $submittedData,
                FormChangeContext::RESULT_FIELD => [],
            ]
        );

        $formChangesProvider = $this->container->get(CompositeFormChangesProvider::class);
        $formChangesProvider
            ->setRequiredDataClass(Payment::class)
            ->setRequiredFields($request->get('updateFields', []))
            ->processFormChanges($context);

        return new JsonResponse($context->getResult());
    }

    /**
     * @param Payment $payment
     * @return FormInterface
     */
    protected function getType(Payment $payment)
    {
        return $this->createForm(PaymentCreateType::class, $payment);
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                CompositeFormChangesProvider::class,
            ]
        );
    }
}
