<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Marello\Bundle\LayoutBundle\Context\FormChangeContext;
use Marello\Bundle\LayoutBundle\Provider\CompositeFormChangesProvider;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Form\Type\OrderType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrderAjaxController extends AbstractController
{
    /**
     * @Route(
     *     path="/form-changes/{id}",
     *     methods={"POST"},
     *     name="marello_order_form_changes",
     *     defaults={"id" = 0}
     * )
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

        $formChangesProvider = $this->container->get(CompositeFormChangesProvider::class);
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
