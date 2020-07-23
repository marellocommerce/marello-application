<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Reader;

use Marello\Bundle\Magento2Bundle\ImportExport\Writer\InternalMagentoOrderWriter;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Psr\Log\LoggerInterface;

class EntityReaderFilterableByInternalMagentoOrder extends EntityReader
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $actionName = 'nothing';

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $actionName
     */
    public function setActionName(string $actionName)
    {
        $this->actionName = $actionName;
    }

    /**
     * @param ContextInterface $context
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $marelloOrderIdsWithMagentoOrderIds = $this->stepExecution
            ->getJobExecution()
            ->getExecutionContext()
            ->get(InternalMagentoOrderWriter::INTERNAL_MAGENTO_ORDER_IDS_CONTEXT) ?? [];

        $flippedMarelloOrderIdsOnExport = \array_flip($context->getOption('ids', []));

        $skippedIds = \array_diff_key(
            $marelloOrderIdsWithMagentoOrderIds,
            $flippedMarelloOrderIdsOnExport
        );

        $magentoOrderIdsOnExport = \array_intersect_key(
            $marelloOrderIdsWithMagentoOrderIds,
            $flippedMarelloOrderIdsOnExport
        );

        $message = <<<MESSAGE
Some Order IDs were skipped, because the current export action 
processes only Marello Order with Internal Magento Order.
MESSAGE;

        if (!empty($skippedIds)) {
            $this->logger->warning(
                $message,
                [
                    'currentActionName' => $this->actionName,
                    'skippedMarelloOrderIds' => \array_keys($skippedIds)
                ]
            );
        }

        $this->setSourceEntityName(
            $context->getOption('entityName'),
            $context->getOption('organization'),
            // in case when no orders allowed use 0 ids to prevent loading any Order
            empty($magentoOrderIdsOnExport) ? [0] : \array_values($magentoOrderIdsOnExport)
        );
    }
}
