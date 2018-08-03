<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Form\Type\OrderType;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class OrderAjaxController extends Controller
{
    /**
     * @Config\Route("/form-changes/{id}", name="marello_order_form_changes", defaults={"id" = 0})
     * @Config\Method({"POST"})
     * @AclAncestor("marello_order_create")
     *
     * @param Request $request
     * @param Order|null $order
     * @return JsonResponse
     */
    public function formChangesAction(Request $request, Order $order = null)
    {
        if (!$order) {
            $order = new Order();
        }

        $form = $this->getType($order);
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
            ->setRequiredDataClass(Order::class)
            ->setRequiredFields($request->get('updateFields'))
            ->processFormChanges($context);

        return new JsonResponse($context->getResult());
    }

    /**
     * @param Order $order
     * @return Form
     */
    protected function getType(Order $order)
    {
        return $this->createForm(OrderType::class, $order);
    }
}
