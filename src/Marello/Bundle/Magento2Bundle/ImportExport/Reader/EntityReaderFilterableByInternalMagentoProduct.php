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
        $productIdsWithInternalProductIds = $this->stepExecution
            ->getJobExecution()
            ->getExecutionContext()
            ->get(InternalMagentoProductWriter::INTERNAL_MAGENTO_PRODUCT_IDS_CONTEXT) ?? [];

        $ids = $context->getOption('ids', []);
        $productIdsWithInternalProduct = \array_keys($productIdsWithInternalProductIds);

        if ($this->skipWithInternalProduct) {
            $message = <<<MESSAGE
The next Product IDs "%s" were skipped, because action "%s" processed only Product without Internal Magento Product.
MESSAGE;
            $conflictIds = \array_intersect($ids, $productIdsWithInternalProduct);
            $ids = \array_diff($ids, $productIdsWithInternalProduct);
        } else {
            $message = <<<MESSAGE
The next Product IDs "%s" were skipped, because action "%s" processed only Product with Internal Magento Product.
MESSAGE;

            $conflictIds = \array_diff($ids, $productIdsWithInternalProduct);
            $ids = \array_intersect($ids, $productIdsWithInternalProduct);
        }

        if (!empty($conflictIds)) {
            $this->logger->warning(
                sprintf($message, implode(', ', $conflictIds), $this->actionName)
            );
        }

        $this->setSourceEntityName(
            $context->getOption('entityName'),
            $context->getOption('organization'),
            empty($ids) ? [0] :  $ids // in case when no product allowed use 0 ids to prevent loading any Product
        );
    }
}
