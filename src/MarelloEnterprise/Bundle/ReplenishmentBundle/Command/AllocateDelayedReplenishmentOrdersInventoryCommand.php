<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Command;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Async\AllocateReplenishmentOrdersInventoryProcessor;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AllocateDelayedReplenishmentOrdersInventoryCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const NAME = 'oro:cron:marello:replenishment:allocate-delayed-orders-inventory';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Allocate inventory for Replenishment Orders with delayed execution DateTime');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $replenishmentOrdersRepository = $this
            ->getContainer()
            ->get('marelloenterprise_replenishment.repository.replenishment_order');
        $messageProducer = $this
            ->getContainer()
            ->get('oro_message_queue.client.message_producer');
        $notAllocatedOrders = $replenishmentOrdersRepository->findNotAllocated();
        
        if (empty($notAllocatedOrders)) {
            $output->writeln('<info>There are no Replenishment Orders to process</info>');
            return;
        }
        $ordersIds = [];
        foreach ($notAllocatedOrders as $order) {
            $ordersIds[] = $order->getId();
        }
        $messageProducer->send(
            AllocateReplenishmentOrdersInventoryProcessor::TOPIC,
            [
                AllocateReplenishmentOrdersInventoryProcessor::ORDERS => $ordersIds,
                'jobId' => md5(rand(1, 5))
            ]
        );
        $output->writeln(
            sprintf(
                '<info>%s</info>',
                'Replenishment Order Configs processed and Replenishment Order creation pushed to message queue'
            )
        );
    }
    
    /**
     * @inheritDoc
     */
    public function getDefaultDefinition()
    {
        return '*/5 * * * *';
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        return true;
    }
}
