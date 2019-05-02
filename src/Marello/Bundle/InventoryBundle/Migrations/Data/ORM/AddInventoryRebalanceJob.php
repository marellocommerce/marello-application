<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Component\MessageQueue\Client\MessageProducer;

use Marello\Bundle\InventoryBundle\Async\Topics;

class AddInventoryRebalanceJob extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // add rebalance job to the queue...
        $this->getMessageProducer()
            ->send(Topics::RESOLVE_REBALANCE_ALL_INVENTORY, Topics::ALL_INVENTORY);
    }

    /**
     * @return MessageProducer
     */
    protected function getMessageProducer()
    {
        return $this->container->get('oro_message_queue.client.message_producer');
    }
}
