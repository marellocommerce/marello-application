<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Reader;

use Marello\Bundle\Magento2Bundle\Entity\Repository\ProductRepository;
use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

class InternalMagentoProductReader extends IteratorBasedReader
{
    /** @var ProductRepository */
    protected $productRepository;

    /** @var ConnectorContextMediator */
    protected $contextMediator;

    /**
     * @param ContextRegistry $contextRegistry
     * @param ProductRepository $productRepository
     * @param ConnectorContextMediator $contextMediator
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        ProductRepository $productRepository,
        ConnectorContextMediator $contextMediator
    ) {
        parent::__construct($contextRegistry);
        $this->productRepository = $productRepository;
        $this->contextMediator = $contextMediator;
    }

    /**
     * {@inheritDoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $channel = $this->contextMediator->getChannel($context);
        if (!$channel) {
            throw new RuntimeException('[Magento 2] MagentoProductReader must have initialized channel!');
        }

        $productIds = $context->getOption('ids', []);

        $magentoProducts = [];
        if (!empty($productIds)) {
            $magentoProducts = $this->productRepository->getMagentoProductByChannelAndProductIds(
                $channel,
                $productIds
            );
        }

        $this->setSourceIterator(new \ArrayIterator($magentoProducts));
    }
}
