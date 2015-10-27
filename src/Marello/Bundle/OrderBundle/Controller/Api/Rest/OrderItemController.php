<?php

namespace Marello\Bundle\OrderBundle\Controller\Api\Rest;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Entity\Repository\OrderItemRepository;
use Marello\Bundle\OrderBundle\Form\Type\OrderItemApiType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\RouteResource("item")
 * @Rest\NamePrefix("marello_order_api_")
 */
class OrderItemController extends FOSRestController
{

    /**
     * Get order items.
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get order items",
     *      resource=true
     * )
     *
     * @param         $order
     * @param Request $request
     *
     * @return Response
     */
    public function cgetAction($order, Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', RestController::ITEMS_PER_PAGE);

        $qb = $this->getDoctrine()
            ->getRepository('MarelloOrderBundle:OrderItem')
            ->getOrderItemsQueryBuilder($order);

        $qb->setMaxResults($limit)
            ->setFirstResult($page > 0 ? ($page - 1) * $limit : 0);

        $result = $qb->getQuery()->execute();

        return new Response(
            $this->get('serializer')->serialize($result, $request->get('_format', 'json')),
            Codes::HTTP_OK
        );
    }

    /**
     * @param         $order
     * @param         $item
     * @param Request $request
     *
     * @return Response
     */
    public function getAction($order, $item, Request $request)
    {
        $entity = $this->getRepository()->findByOrderAndId($order, $item);

        if ($entity === null) {
            return new Response('', Codes::HTTP_NOT_FOUND);
        }

        return new Response(
            $this->get('serializer')->serialize($entity, $request->get('_format', 'json')),
            Codes::HTTP_OK
        );
    }

    /**
     * Create new order item.
     *
     * @ApiDoc(
     *      description="Create order item.",
     *      resource=true
     * )
     *
     * @param Order   $order
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Order $order, Request $request)
    {
        $orderItem = new OrderItem();
        $orderItem->setOrder($order);

        return $this->update($orderItem, $request, $order);
    }

    /**
     * @param         $order
     * @param         $item
     * @param Request $request
     *
     * @return Response
     */
    public function putAction($order, $item, Request $request)
    {
        $orderItem = $this->getRepository()->findByOrderAndId($order, $item);

        return $this->update($orderItem, $request, null);
    }

    protected function update(OrderItem $orderItem, Request $request, Order $order = null)
    {
        $form = $this->createForm(OrderItemApiType::NAME, $orderItem);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($order) {
                $order->addItem($orderItem);
            }

            $this->getManager()->persist($order ? : $orderItem->getOrder());
            $this->getManager()->flush();

            return new Response('', Codes::HTTP_OK);
        }

        return new Response('', Codes::HTTP_BAD_REQUEST);
    }

    public function deleteAction($order, $item)
    {
        $orderItem = $this->getRepository()->findByOrderAndId($order, $item);

        $this->getManager()->remove($orderItem);
        $this->getManager()->flush();

        return new Response('', Codes::HTTP_OK);
    }

    /**
     * @return OrderItemRepository
     */
    protected function getRepository()
    {
        return $this->getManager()->getRepository('MarelloOrderBundle:OrderItem');
    }

    /**
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManager();
    }
}
