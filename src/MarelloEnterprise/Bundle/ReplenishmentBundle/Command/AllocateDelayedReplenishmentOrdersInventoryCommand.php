<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Async\AllocateReplenishmentOrdersInventoryProcessor;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\ReplenishmentOrder;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AllocateDelayedReplenishmentOrdersInventoryCommand extends Command implements CronCommandInterface
{
    const NAME = 'oro:cron:marello:replenishment:allocate-delayed-orders-inventory';

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;

    /**
     * @param Registry $doctrine
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(
        Registry $doctrine,
        MessageProducerInterface $messageProducer
    ) {
        $this->doctrine = $doctrine;
        $this->messageProducer = $messageProducer;

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
            return;
        }
        $ordersIds = [];
        foreach ($notAllocatedOrders as $order) {
            $ordersIds[] = $order->getId();
        }
        $this->messageProducer->send(
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

        return 0;
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
