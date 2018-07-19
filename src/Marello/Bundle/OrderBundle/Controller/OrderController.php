<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class OrderController extends Controller
{
    /**
     * @Config\Route("/", name="marello_order_order_index")
     * @Config\Template
     * @AclAncestor("marello_order_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloOrderBundle:Order'];
    }

    /**
     * @Config\Route("/view/{id}", requirements={"id"="\d+"}, name="marello_order_order_view")
     * @Config\Template
     * @AclAncestor("marello_order_view")
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
     * @Config\Route("/create", name="marello_order_order_create")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @AclAncestor("marello_order_create")
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
     * @Config\Route("/update/{id}", requirements={"id"="\d+"}, name="marello_order_order_update")
     * @Config\Method({"GET", "POST"})
     * @Config\Template
     * @AclAncestor("marello_order_update")
     *
     * @param Request $request
     * @param Order   $order
     *
     * @return array
     */
    public function updateAction(Request $request, Order $order)
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
        $formName = $order ? 'marello_order_update' : 'marello_order_order';

        if ($order === null) {
            $order = new Order();
        }

        /*
         * Copy of original order items collection to be used as a reference to find detached order items.
         */
        $originalItems = new ArrayCollection($order->getItems()->toArray());

        $form = $this->createForm($formName, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('marello.order.messages.success.order.saved')
            );
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
                    'route'      => 'marello_order_order_update',
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

    /**
     * @Config\Route(
     *     "/widget/address/{id}/{typeId}",
     *     requirements={"id"="\d+","typeId"="\d+"},
     *     name="marello_order_order_address"
     * )
     * @Config\Method({"GET", "POST"})
     * @Config\Template("MarelloOrderBundle:Order/widget:address.html.twig")
     * @AclAncestor("marello_order_view")
     *
     * @param Request $request
     * @param MarelloAddress $address
     *
     * @return array
     */
    public function addressAction(Request $request, MarelloAddress $address)
    {
        return [
            'orderAddress' => $address,
            'addressType' => ((int)$request->get('typeId') === 1) ? 'billing' : 'shipping'
        ];
    }
    
    /**
     * @Config\Route("/update/address/{id}", requirements={"id"="\d+"}, name="marello_order_order_updateaddress")
     * @Config\Method({"GET", "POST"})
     * @Config\Template("MarelloOrderBundle:Order:widget/updateAddress.html.twig")
     * @AclAncestor("marello_order_update")
     *
     * @param Request $request
     * @param MarelloAddress $address
     *
     * @return array
     */
    public function updateAddressAction(Request $request, MarelloAddress $address)
    {
        $responseData = array(
            'saved' => false
        );
        $form  = $this->createForm('marello_address', $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $responseData['orderAddress'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $form->createView();
        return $responseData;
    }
}
