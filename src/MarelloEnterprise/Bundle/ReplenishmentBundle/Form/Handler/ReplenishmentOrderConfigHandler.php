<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderItem;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Strategy\ReplenishmentStrategiesRegistry;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ReplenishmentOrderConfigHandler
{
    use RequestHandlerTrait;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var ReplenishmentStrategiesRegistry
     */
    protected $replenishmentStrategiesRegistry;

    /**
     * @param FormInterface $form
     * @param RequestStack $requestStack
     * @param ObjectManager $manager
     * @param ReplenishmentStrategiesRegistry $replenishmentStrategiesRegistry
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        ObjectManager $manager,
        ReplenishmentStrategiesRegistry $replenishmentStrategiesRegistry
    ) {
        $this->form = $form;
        $this->request = $requestStack->getCurrentRequest();
        $this->manager = $manager;
        $this->replenishmentStrategiesRegistry = $replenishmentStrategiesRegistry;
    }

    /**
     * Process form
     *
     * @param  ReplenishmentOrderConfig $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(ReplenishmentOrderConfig $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($this->form, $this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    public function getFormView()
    {
        return $this->form->createView();
    }

    /**
     * "Success" form handler
     *
     * @param ReplenishmentOrderConfig $entity
     */
    protected function onSuccess(ReplenishmentOrderConfig $entity)
    {
        $this->manager->persist($entity);
        $strategy = $this->replenishmentStrategiesRegistry->getStrategy($entity->getStrategy());
        $replenishmentResults = $strategy->getResults($entity);
        $orders = [];
        foreach ($replenishmentResults as $result) {
            /** @var Warehouse $origin */
            $origin = $result['origin'];
            /** @var Warehouse $destination */
            $destination = $result['destination'];
            /** @var Product $product */
            $product = $result['product'];

            if (!isset($orders[sprintf('%s-%s', $origin->getId(), $destination->getId())])) {
                $order = new ReplenishmentOrder();
                $order
                    ->setOrganization($entity->getOrganization())
                    ->setOrigin($origin)
                    ->setDestination($destination)
                    ->setExecutionDate($entity->getExecutionDate())
                    ->setPercentage($entity->getPercentage())
                    ->setReplOrderConfig($entity);
                $orders[sprintf('%s-%s', $origin->getId(), $destination->getId())] = $order;
            }
            /** @var ReplenishmentOrder $order */
            $order = $orders[sprintf('%s-%s', $origin->getId(), $destination->getId())];
            $orderItem = new ReplenishmentOrderItem();
            $orderItem
                ->setInventoryQty($result['quantity'])
                ->setTotalInventoryQty($result['total_quantity'])
                ->setProduct($product)
                ->setOrder($order);
            $order->addReplOrderItem($orderItem);
        }
        if ($entity->getId()) {
            $existingOrders = $this->manager->getRepository(ReplenishmentOrder::class)->findByConfig($entity->getId());
            foreach ($existingOrders as $existingOrder) {
                $this->manager->remove($existingOrder);
            }
        }
        foreach ($orders as $order) {
            $this->manager->persist($order);
        }
        $this->manager->flush();
    }
}
