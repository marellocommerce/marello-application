<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\CoreBundle\Model\JobIdGenerationTrait;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Async\AllocateReplenishmentOrdersInventoryProcessor;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Async\Topic\AllocateReplenishmentOrdersInventoryTopic;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use Oro\Bundle\CronBundle\Command\CronCommandScheduleDefinitionInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AllocateDelayedReplenishmentOrdersInventoryCommand extends Command implements CronCommandScheduleDefinitionInterface
{
    use JobIdGenerationTrait;

    const NAME = 'oro:cron:marello:replenishment:allocate-delayed-orders-inventory';

    public function __construct(
        private ManagerRegistry $doctrine,
        private MessageProducerInterface $messageProducer
    ) {
        parent::__construct();
    }

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
        $notAllocatedOrders = $this->doctrine
            ->getManagerForClass(ReplenishmentOrder::class)
            ->getRepository(ReplenishmentOrder::class)
            ->findNotAllocated();
        
        if (empty($notAllocatedOrders)) {
            $output->writeln('<info>There are no Replenishment Orders to process</info>');
            return self::SUCCESS;
        }
        $ordersIds = [];
        foreach ($notAllocatedOrders as $order) {
            $ordersIds[] = $order->getId();
        }
        $this->messageProducer->send(
            AllocateReplenishmentOrdersInventoryTopic::getName(),
            [
                AllocateReplenishmentOrdersInventoryProcessor::ORDERS => $ordersIds,
                'jobId' => $this->generateJobId(rand(1, 5))
            ]
        );
        $output->writeln(
            sprintf(
                '<info>%s</info>',
                'Replenishment Order Configs processed and Replenishment Order creation pushed to message queue'
            )
        );

        return self::SUCCESS;
    }
    
    /**
     * @inheritDoc
     */
    public function getDefaultDefinition()
    {
        return '*/5 * * * *';
    }
}
