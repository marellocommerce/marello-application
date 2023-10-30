<?php

namespace Marello\Bundle\OrderBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\AddressBundle\Form\Type\AddressType;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Form\Type\OrderType;
use Marello\Bundle\OrderBundle\Form\Type\OrderUpdateType;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\CurrencyBundle\Utils\CurrencyNameHelper;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UIBundle\Route\Router;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderController extends AbstractController
{
    /**
     * @Route(
     *     path="/",
     *     name="marello_order_order_index"
     * )
     * @Template
     * @AclAncestor("marello_order_view")
     */
    public function indexAction()
    {
        return ['entity_class' => 'MarelloOrderBundle:Order'];
    }

    /**
     * @Route(
     *     path="/view/{id}",
     *     requirements={"id"="\d+"},
     *     name="marello_order_order_view"
     * )
     * @Template
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
     * @Route(
     *     path="/create",
     *     methods={"GET", "POST"},
     *     name="marello_order_order_create"
     * )
     * @Template
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
     * @Route(
     *     path="/update/{id}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_order_order_update"
     * )
     * @Template
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
        $formClass = $order ? OrderUpdateType::class : OrderType::class;

        if ($order === null) {
            $order = new Order();
        }

        /*
         * Copy of original order items collection to be used as a reference to find detached order items.
         */
        $originalItems = new ArrayCollection($order->getItems()->toArray());

        $form = $this->createForm($formClass, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('marello.order.messages.success.order.saved')
            );
            $manager = $this->container->get(ManagerRegistry::class)->getManager();

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

            return $this->container->get(Router::class)->redirect($order);
        }

        return [
            'entity' => $order,
            'form'   => $form->createView(),
        ];
    }

    /**
     * @Route(
     *     path="/widget/address/{id}/{typeId}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+","typeId"="\d+"},
     *     name="marello_order_order_address"
     * )
     * @Template("@MarelloOrder/Order/widget/address.html.twig")
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
     * @Route(
     *     path="/update/address/{id}",
     *     methods={"GET", "POST"},
     *     requirements={"id"="\d+"},
     *     name="marello_order_order_updateaddress"
     * )
     * @Template("@MarelloOrder/Order/widget/updateAddress.html.twig")
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
        $form  = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->container->get(ManagerRegistry::class)->getManager()->flush();
            $responseData['orderAddress'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $form->createView();
        return $responseData;
    }

    /**
     * @Route(
     *      path="/widget/products",
     *      name="marello_order_widget_products_by_channel",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @AclAncestor("marello_product_view")
     * @Template("@MarelloOrder/Order/widget/productsByChannel.html.twig")
     * @return array
     */
    public function productsByChannelAction(Request $request)
    {
        $channel = $this->container->get(ManagerRegistry::class)
            ->getManagerForClass(SalesChannel::class)
            ->getRepository(SalesChannel::class)
            ->find($request->get('channelId'));

        if ($channel) {
            return [
                'channelId' => $channel->getId(),
                'currency' => $this->container
                    ->get(CurrencyNameHelper::class)
                    ->getCurrencyName($channel->getCurrency()),
            ];
        }

        return [
            'channelId' => null,
            'currency' => null,
        ];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                Router::class,
                ManagerRegistry::class,
                CurrencyNameHelper::class,
            ]
        );
    }
}
