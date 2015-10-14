<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Marello\Bundle\OrderBundle\Entity\Order;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends Controller
{
    /**
     * @Config\Route("/")
     * @Config\Template
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloOrderBundle:Order'];
    }

    /**
     * @Config\Route("/{id}", requirements={"id"="\d+"})
     * @Config\Template
     *
     * @param Order $order
     *
     * @return array
     */
    public function viewAction(Order $order)
    {
        return ['entity' => $order];
    }

    /**
     * @Config\Route("/{id}/edit", requirements={"id"="\d+"})
     * @Config\Template
     *
     * @param Request $request
     * @param Order   $order
     *
     * @return array
     */
    public function editAction(Request $request, Order $order)
    {
        return $this->update($request, $order);
    }

    /**
     * Handles order updates and creation.
     *
     * @param Request    $request
     * @param Order|null $order
     *
     * @return array
     */
    protected function update(Request $request, Order $order = null)
    {
        if ($order === null) {
            $order = new Order();
        }

        $form = $this->createForm('marello_order_order', $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($order);
            $manager->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route'      => 'marello_order_order_edit',
                    'parameters' => [
                        'id' => $order->getId(),
                    ],
                ],
                [
                    'route' => 'marello_order_order_index',
                ],
                $order
            );
        }

        return [
            'entity' => $order,
            'form'   => $form->createView(),
        ];
    }
}
