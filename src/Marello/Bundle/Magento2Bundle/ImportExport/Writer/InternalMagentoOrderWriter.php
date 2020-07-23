<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Marello\Bundle\Magento2Bundle\DTO\OrderIdentifierDTO;

class InternalMagentoOrderWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var string */
    public const INTERNAL_MAGENTO_ORDER_IDS_CONTEXT = 'internalMagentoOrderIDs';

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param OrderIdentifierDTO[] $items
     */
    public function write(array $items)
    {
        $existedMagentoOrderIds = $this->stepExecution
            ->getJobExecution()
            ->getExecutionContext()
            ->get(self::INTERNAL_MAGENTO_ORDER_IDS_CONTEXT) ?? [];

        foreach ($items as $item) {
            $existedMagentoOrderIds[$item->getMarelloOrderId()] = $item->getMagentoOrderId();
        }

        $this->stepExecution
            ->getJobExecution()
            ->getExecutionContext()
            ->put(self::INTERNAL_MAGENTO_ORDER_IDS_CONTEXT, $existedMagentoOrderIds);
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
