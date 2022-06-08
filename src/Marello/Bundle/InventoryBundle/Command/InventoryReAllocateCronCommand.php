<?php

namespace Marello\Bundle\InventoryBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;
use Marello\Bundle\WorkflowBundle\Async\Topics;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider;

class InventoryReAllocateCronCommand extends Command implements CronCommandInterface
{
    const COMMAND_NAME = 'oro:cron:marello:inventory:reallocate';
    const WORKFLOW_STEP_FROM = 'pending';
    const WORKFLOW_NAME = 'marello_allocate_workflow';
    const WORKFLOW_RE_ALLOCATE_STEP = 'reallocate';
    const EXIT_CODE = 0;

    /** @var Registry $registry */
    protected $registry;

    /** @var InventoryAllocationProvider $allocationProvider */
    protected $allocationProvider;

    /** @var OrderWarehousesProviderInterface $warehousesProvider */
    protected $warehousesProvider;

    /** @var MessageProducerInterface $messageProducer */
    protected $messageProducer;

    /**
     * InventoryReAllocateCronCommand constructor.
     * @param Registry $registry
     * @param InventoryAllocationProvider $allocationProvider
     * @param OrderWarehousesProviderInterface $warehousesProvider
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(
        Registry $registry,
        InventoryAllocationProvider $allocationProvider,
        OrderWarehousesProviderInterface $warehousesProvider,
        MessageProducerInterface $messageProducer
    ) {
        parent::__construct();
        
        $this->registry = $registry;
        $this->allocationProvider = $allocationProvider;
        $this->warehousesProvider = $warehousesProvider;
        $this->messageProducer = $messageProducer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Re allocate');
    }

    /**
     * @inheritDoc
     */
    public function getDefaultDefinition()
    {
        // tmp every 10 minutes
        return '*/10 * * * *';
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $allocations = $this
            ->registry
            ->getRepository(Allocation::class)
            ->findBy(['state' => 'waiting']);

        /** @var Allocation $allocation */
        foreach ($allocations as $allocation) {
            foreach ($this->warehousesProvider->getWarehousesForOrder($allocation->getOrder(), $allocation) as $orderWarehouseResults) {
                foreach ($orderWarehouseResults as $result) {
                    if (!in_array($result->getWarehouse()->getCode(), ['no_warehouse', 'could_not_allocate'])) {
                        /** @var WorkflowItem $workflowItem */
                        $workflowItem = $this->registry
                            ->getRepository(WorkflowItem::class)
                            ->findOneBy(['entityId' => $allocation->getId()]);
                        $this->messageProducer->send(
                            Topics::WORKFLOW_TRANSIT_TOPIC,
                            [
                                'workflow_item_entity_id' => $allocation->getId(),
                                'current_step_id' => $workflowItem->getCurrentStep()->getId(),
                                'entity_class' => Allocation::class,
                                'transition' => self::WORKFLOW_RE_ALLOCATE_STEP,
                                'jobId' => md5($allocation->getId()),
                                'priority' => MessagePriority::NORMAL
                            ]
                        );
                    }
                }
            }
        }

        return self::EXIT_CODE;
    }
}
