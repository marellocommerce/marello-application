<?php

namespace Marello\Bundle\InventoryBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ReshipmentHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    public function __construct(
        protected EntityManagerInterface $manager,
        protected InventoryAllocationProvider $allocationProvider
    ) {}

    public function process($data, FormInterface $form, Request $request)
    {
        if (!$data instanceof Order) {
            throw new \InvalidArgumentException('Argument data should be instance of Order entity');
        }

        $form->setData($data);

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($form, $request);

            if ($form->isValid()) {
                $this->onSuccess($data);

                return true;
            }
        }

        return false;
    }

    protected function onSuccess(Order $entity)
    {
        $this->allocationProvider->allocateOrderToWarehouses($entity, null, function (Order $order) {
            // Revert all order changes back
            foreach ($order->getItems() as $item) {
                $this->manager->refresh($item);
            }
            $this->manager->refresh($order->getShippingAddress());
            $this->manager->refresh($order);
        });

        $this->manager->flush();
    }
}
