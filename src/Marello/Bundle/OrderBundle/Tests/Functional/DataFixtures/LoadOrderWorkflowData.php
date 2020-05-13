<?php

namespace Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class LoadOrderWorkflowData extends AbstractFixture implements DependentFixtureInterface
{

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrderData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $orders = $manager->getRepository(Order::class)->findAll();
        $workflowItemRepo = $manager->getRepository(WorkflowItem::class);
        $i = 0;
        foreach ($orders as $order) {
            $workflowItems = $workflowItemRepo->findBy(['entityId' => $order->getId()]);
            /** @var WorkflowItem $workflowItem */
            $workflowItem = array_shift($workflowItems);
            $this->setReference(sprintf('marello_order_workflow_item_%s', $i++), $workflowItem);
        }
    }
}
