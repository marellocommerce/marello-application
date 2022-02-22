<?php

namespace Marello\Bundle\RefundBundle\Controller;

use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Form\Type\RefundType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RefundAjaxController extends AbstractController
{

    /**
     * @Route(
     *     path="/form-changes/{id}",
     *     methods={"POST"},
     *     name="marello_refund_form_changes",
     *     defaults={"id" = 0}
     * )
     * @AclAncestor("marello_refund_update")
     *
     * @param Request $request
     * @param Refund|null $order
     * @return JsonResponse
     */
    public function formChangesAction(Request $request, Refund $refund = null)
    {
        if (!$refund) {
            $refund = new Refund();
            $form = $this->getType($refund);
        } else {
            $form = $this->getType($refund);
            $submittedData = $request->get($form->getName());
            $form->submit($submittedData);
        }

        $context = new FormChangeContext(
            [
                FormChangeContext::FORM_FIELD => $form,
                FormChangeContext::SUBMITTED_DATA_FIELD => $submittedData,
                FormChangeContext::RESULT_FIELD => [],
            ]
        );

        $formChangesProvider = $this->get('marello_layout.provider.form_changes_data.composite');
        $formChangesProvider
            ->setRequiredDataClass(Refund::class)
            ->setRequiredFields($request->get('updateFields', []))
            ->processFormChanges($context);

        return new JsonResponse($context->getResult());
    }

    /**
     * @Route(
     *     path="/form-create/{id}",
     *     methods={"POST"},
     *     name="marello_refund_form_create",
     *     defaults={"id" = 0}
     * )
     * @AclAncestor("marello_refund_create")
     *
     * @param Request $request
     * @param Order|null $order
     * @return JsonResponse
     */
    public function formCreateAction(Request $request, Order $order = null)
    {
        $refund = Refund::fromOrder($order);
        $form = $this->getType($refund);
        $submittedData = $request->get($form->getName());

        $form->submit($submittedData);

        $context = new FormChangeContext(
            [
                FormChangeContext::FORM_FIELD => $form,
                FormChangeContext::SUBMITTED_DATA_FIELD => $submittedData,
                FormChangeContext::RESULT_FIELD => [],
            ]
        );

        $formChangesProvider = $this->get('marello_layout.provider.form_changes_data.composite');
        $formChangesProvider
            ->setRequiredDataClass(Refund::class)
            ->setRequiredFields($request->get('updateFields', []))
            ->processFormChanges($context);

        return new JsonResponse($context->getResult());
    }

    /**
     * @param Refund $refund
     * @return FormInterface
     */
    protected function getType(Refund $refund)
    {
        return $this->createForm(RefundType::class, $refund);
    }
}
