<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Command;

use MarelloEnterprise\Bundle\ReplenishmentBundle\Async\CreateReplenishmentOrdersProcessor;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDelayedReplenishmentOrdersCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const NAME = 'oro:cron:marello:replenishment:create-delayed-orders';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Creates Replenishment Orders with delayed execution DateTime');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $replenishmentOrderConfigRepository = $this
            ->getContainer()
            ->get('marelloenterprise_replenishment.repository.replenishment_order_config');
        $messageProducer = $this
            ->getContainer()
            ->get('oro_message_queue.client.message_producer');
        $notExecutedConfigs = $replenishmentOrderConfigRepository->findNotExecuted();
        
        if (empty($notExecutedConfigs)) {
            $output->writeln('<info>There are no Replenishment Order Configs to process</info>');
            return;
        }
        $configIds = [];
        foreach ($notExecutedConfigs as $config) {
            $configIds[] = $config->getId();
        }
        $messageProducer->send(
            CreateReplenishmentOrdersProcessor::TOPIC,
            [
                CreateReplenishmentOrdersProcessor::CONFIGS => $configIds,
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
