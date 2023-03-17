<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrderConfig;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Provider\ReplenishmentOrdersFromConfigProvider;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ReplenishmentOrderConfigHandler
{
    use RequestHandlerTrait;

    public function __construct(
        protected ObjectManager $manager,
        protected ReplenishmentOrdersFromConfigProvider $replenishmentOrdersProvider
    ) {}

    public function process(FormInterface $form, Request $request): array
    {
        if (in_array($request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($form, $request);

            if ($form->isValid()) {
                return $this->onSuccess($form->getData());
            }
        }

        return [
            'result' => false,
        ];
    }

    protected function onSuccess(ReplenishmentOrderConfig $entity)
    {
        if (!$entity->getExecutionDateTime()) {
            $entity->setExecutionDateTime(new \DateTime());
        }
        $this->manager->persist($entity);
        $orders = $this->replenishmentOrdersProvider->getReplenishmentOrders($entity);

        if (empty($orders)) {
            return [
                'result' => true,
                'messageType' => 'info',
                'message'
                    => 'marelloenterprise.replenishment.replenishmentorderconfig.messages.info.no_products_in_origins'
            ];
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

        return [
            'result' => true,
            'messageType' => 'success',
            'message' => 'marelloenterprise.replenishment.replenishmentorderconfig.messages.success.saved'
        ];
    }
}
