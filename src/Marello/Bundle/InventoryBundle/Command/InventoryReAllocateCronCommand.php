<?php

namespace Marello\Bundle\InventoryBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\InventoryBundle\Provider\InventoryAllocationProvider;
use Marello\Bundle\InventoryBundle\Provider\AllocationStateStatusInterface;
use Marello\Bundle\InventoryBundle\Provider\OrderWarehousesProviderInterface;

class InventoryReAllocateCronCommand extends Command //implements CronCommandInterface
{
    const COMMAND_NAME = 'oro:cron:marello:inventory:reallocate';
    const WORKFLOW_STEP_FROM = 'pending';
    const WORKFLOW_NAME = 'marello_allocate_workflow';
    const WORKFLOW_RE_ALLOCATE_STEP = 'reallocate';
    const EXIT_CODE = 0;

    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /** @var InventoryAllocationProvider $allocationProvider */
    protected $allocationProvider;

    /** @var OrderWarehousesProviderInterface $warehousesProvider */
    protected $warehousesProvider;

    /**
     * InventoryReAllocateCronCommand constructor.
     * @param DoctrineHelper $doctrineHelper
     * @param InventoryAllocationProvider $allocationProvider
     * @param OrderWarehousesProviderInterface $warehousesProvider
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        InventoryAllocationProvider $allocationProvider,
        OrderWarehousesProviderInterface $warehousesProvider
    ) {
        parent::__construct();
        
        $this->doctrineHelper = $doctrineHelper;
        $this->allocationProvider = $allocationProvider;
        $this->warehousesProvider = $warehousesProvider;
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
            ->doctrineHelper
            ->getEntityRepositoryForClass(Allocation::class)
            ->findBy(['state' => 'waiting']);

        $em = $this->doctrineHelper->getEntityManagerForClass(Allocation::class);
        /** @var Allocation $allocation */
        foreach ($allocations as $allocation) {
            foreach ($this->warehousesProvider->getWarehousesForOrder($allocation->getOrder()) as $orderWarehouseResults) {
                foreach ($orderWarehouseResults as $result) {
                    if (!in_array($result->getWarehouse()->getCode(), ['no_warehouse', 'could_not_allocate'])) {
                        //$this->allocationProvider->allocateOrderToWarehouses($allocation->getOrder());
                        // todo only generate allocation results once
                        $allocation->setState($this->getEnumValue('marello_allocation_state', AllocationStateStatusInterface::ALLOCATION_STATE_CLOSED));
                        $allocation->setStatus($this->getEnumValue('marello_allocation_status', AllocationStateStatusInterface::ALLOCATION_STATUS_CLOSED));
                        $em->persist($allocation);
                    }
                }
            }
        }

        $em->flush();

        return self::EXIT_CODE;
    }

    /**
     * @param $enumClass
     * @param $value
     * @return object|null
     */
    protected function getEnumValue($enumClass, $value)
    {
        $className = ExtendHelper::buildEnumValueClassName($enumClass);
        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $this->doctrineHelper
            ->getEntityRepositoryForClass($className);

        return $enumRepo->findOneBy(['id' => $value]);
    }
}
