<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Reader;

use Marello\Bundle\Magento2Bundle\ImportExport\Writer\InternalMagentoProductWriter;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Psr\Log\LoggerInterface;

class EntityReaderFilterableByInternalMagentoProduct extends EntityReader
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $actionName = 'nothing';

    /** @var string */
    protected $skipWithInternalProduct = false;

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
     * @param bool $skipWithInternalProduct
     */
    public function setSkipWithInternalProduct(bool $skipWithInternalProduct)
    {
        $this->skipWithInternalProduct = $skipWithInternalProduct;
    }

    /**
     * @param ContextInterface $context
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $marelloProductIdsWithMagentoProductIds = $this->stepExecution
            ->getJobExecution()
            ->getExecutionContext()
            ->get(InternalMagentoProductWriter::INTERNAL_MAGENTO_PRODUCT_IDS_CONTEXT) ?? [];

        $marelloProductIdsOnExport = $context->getOption('ids', []);
        $marelloProductIdsWithInternalProduct = \array_keys($marelloProductIdsWithMagentoProductIds);

        if ($this->skipWithInternalProduct) {
            $message = <<<MESSAGE
Some Product IDs were skipped, because the current export action 
processes only Marello Product without Internal Magento Product.
MESSAGE;
            $marelloSkippedProductIds = \array_intersect(
                $marelloProductIdsOnExport,
                $marelloProductIdsWithInternalProduct
            );
            $newMarelloProductIdsOnExport = \array_diff(
                $marelloProductIdsOnExport,
                $marelloProductIdsWithInternalProduct
            );
        } else {
            $message = <<<MESSAGE
Some Product IDs were skipped, because the current export action 
processes only Marello Product with Internal Magento Product.
MESSAGE;

            $marelloSkippedProductIds = \array_diff(
                $marelloProductIdsOnExport,
                $marelloProductIdsWithInternalProduct
            );
            $newMarelloProductIdsOnExport = \array_intersect(
                $marelloProductIdsOnExport,
                $marelloProductIdsWithInternalProduct
            );
        }

        if (!empty($marelloSkippedProductIds)) {
            $this->logger->warning(
                $message,
                [
                    'currentActionName' => $this->actionName,
                    'skippedProductIds' => $marelloSkippedProductIds
                ]
            );
        }

        $this->setSourceEntityName(
            $context->getOption('entityName'),
            $context->getOption('organization'),
            // in case when no product allowed use 0 ids to prevent loading any Product
            empty($newMarelloProductIdsOnExport) ? [0] : $newMarelloProductIdsOnExport
        );
    }
}
