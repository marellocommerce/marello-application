<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Command;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Async\AllocateReplenishmentOrdersInventoryProcessor;
use MarelloEnterprise\Bundle\ReplenishmentBundle\Entity\Repository\ReplenishmentOrderRepository;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AllocateDelayedReplenishmentOrdersInventoryCommand extends Command implements CronCommandInterface
{
    const NAME = 'oro:cron:marello:replenishment:allocate-delayed-orders-inventory';

    /**
     * @var ReplenishmentOrderRepository
     */
    private $replenishmentOrdersRepository;

    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;

    /**
     * @param ReplenishmentOrderRepository $replenishmentOrderRepository
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(
        ReplenishmentOrderRepository $replenishmentOrderRepository,
        MessageProducerInterface $messageProducer
    ) {
        $this->replenishmentOrdersRepository = $replenishmentOrderRepository;
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
        $notAllocatedOrders = $this->replenishmentOrdersRepository->findNotAllocated();
        
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
