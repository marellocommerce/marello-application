<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
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
     * @Config\Route("/create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template("MarelloOrderBundle:Order:edit.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        return $this->update($request);
    }

    /**
     * @Config\Route("/{id}/edit", requirements={"id"="\d+"})
     * @Config\Method({"GET", "POST"})
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
     * @param Request $request
     * @param Order   $order
     *
     * @return array
     */
    protected function update(Request $request, Order $order = null)
    {
        if ($order === null) {
            $order = new Order();
        }

        /*
         * Copy of original order items collection to be used as a reference to find detached order items.
         */
        $originalItems = new ArrayCollection($order->getItems()->toArray());

        $form = $this->createForm('marello_order_order', $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            /*
             * Remove detached order items.
             */
            $originalItems->filter(function (OrderItem $originalItem) use ($order) {
                return false === $order->getItems()->contains($originalItem);
            })->map(function (OrderItem $orderItem) use ($manager) {
                $manager->remove($orderItem);
            });

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
                    'route'      => 'marello_order_order_view',
                    'parameters' => [
                        'id' => $order->getId(),
                    ],
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
