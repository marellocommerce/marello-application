<?php

namespace Marello\Bundle\InventoryBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider;

class ReshipmentHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    public function __construct(
        protected EntityManagerInterface $manager,
        protected InventoryAllocationProvider $allocationProvider,
        protected array $removedItems = []
    ) {}

    /**
     * @param $data
     * @param FormInterface $form
     * @param Request $request
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
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

    /**
     * @param Order $entity
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function onSuccess(Order $entity)
    {
        // Temporary remove items with quantity = 0
        foreach ($entity->getItems() as $item) {
            if (!$item->getQuantity()) {
                $entity->getItems()->removeElement($item);
                $this->removedItems[] = $item;
            }
        }

        $this->allocationProvider->allocateOrderToWarehouses($entity, null, function (Order $order) {
            // Revert all order changes back
            foreach ($this->removedItems as $item) {
                $order->getItems()->add($item);
            }
            $this->removedItems = [];
            foreach ($order->getItems() as $item) {
                $this->manager->refresh($item);
            }
            $this->manager->refresh($order->getShippingAddress());
            $this->manager->refresh($order);
        });

        $this->manager->flush();
    }
}
