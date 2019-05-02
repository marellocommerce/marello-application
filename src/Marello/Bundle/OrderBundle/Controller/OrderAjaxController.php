<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Form\Type\OrderType;
use Marello\Bundle\LayoutBundle\Context\FormChangeContext;

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
            ->setRequiredFields($request->get('updateFields', []))
            ->processFormChanges($context);

        return new JsonResponse($context->getResult());
    }

    /**
     * @param Order $order
     * @return FormInterface
     */
    protected function getType(Order $order)
    {
        return $this->createForm(OrderType::class, $order);
    }
}
