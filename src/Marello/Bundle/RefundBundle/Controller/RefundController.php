<?php

namespace Marello\Bundle\RefundBundle\Controller;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Form\Type\RefundType;
use Oro\Bundle\SecurityBundle\Annotation as Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RefundController extends Controller
{
    /**
     * @Config\Route("/", name="marello_refund_index")
     * @Config\Template
     * @Security\AclAncestor("marello_refund_view")
     */
    public function indexAction()
    {
        return [
            'entity_class' => Refund::class,
        ];
    }

    /**
     * @Config\Route("/create/{id}", name="marello_refund_create")
     * @Config\Template
     * @Security\AclAncestor("marello_refund_create")
     *
     * @param Request $request
     * @param Order   $order
     *
     * @return array
     */
    public function createAction(Request $request, Order $order)
    {
        $entity = new Refund();
        $entity
            ->setOrder($order)
            ->setCustomer($order->getCustomer());

        $form = $this->createForm(RefundType::NAME, $entity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('marello_refund_index');
        }

        return compact('form', 'entity');
    }
}
