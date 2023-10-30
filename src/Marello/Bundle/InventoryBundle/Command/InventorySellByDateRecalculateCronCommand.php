<?php

namespace Marello\Bundle\InventoryBundle\Command;

use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Manager\InventoryManagerInterface;
use Marello\Bundle\InventoryBundle\Model\InventoryLevelCalculator;
use Oro\Bundle\CronBundle\Command\CronCommandScheduleDefinitionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class InventorySellByDateRecalculateCronCommand extends Command implements CronCommandScheduleDefinitionInterface
{
    const COMMAND_NAME = 'oro:cron:marello:inventory:sell-by-date-recalculate';

    public function __construct(
        protected DoctrineHelper $doctrineHelper,
        protected InventoryLevelCalculator $inventoryLevelCalculator,
        protected InventoryManagerInterface $inventoryManager
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Recalculate inventory levels according to a sell-by-date value for inventory batches');
    }

    public function getDefaultDefinition()
    {
        return '0 5 * * *';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inventoryLevels = $this
            ->doctrineHelper
            ->getEntityRepositoryForClass(InventoryLevel::class)
            ->findWithExpiredSellByDateBatch();

        foreach ($inventoryLevels as $inventoryLevel) {
            $batches = $inventoryLevel->getInventoryBatches()->toArray();
            $batchInventory = $this->inventoryLevelCalculator->calculateBatchInventoryLevelQty($batches);
            $this->inventoryManager->updateInventory($inventoryLevel, $batchInventory);
        }

        $this->doctrineHelper->getEntityManagerForClass(InventoryLevel::class)->flush();

        return self::SUCCESS;
    }
}
