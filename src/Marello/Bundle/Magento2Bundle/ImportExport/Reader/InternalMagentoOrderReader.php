<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Reader;

use Marello\Bundle\Magento2Bundle\Entity\Repository\OrderRepository;
use Marello\Bundle\Magento2Bundle\Exception\InvalidArgumentException;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

class InternalMagentoOrderReader extends IteratorBasedReader
{
    /** @var OrderRepository */
    protected $orderRepository;

    /** @var ConnectorContextMediator */
    protected $contextMediator;

    /**
     * @param ContextRegistry $contextRegistry
     * @param OrderRepository $orderRepository
     * @param ConnectorContextMediator $contextMediator
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        OrderRepository $orderRepository,
        ConnectorContextMediator $contextMediator
    ) {
        parent::__construct($contextRegistry);
        $this->orderRepository = $orderRepository;
        $this->contextMediator = $contextMediator;
    }

    /**
     * {@inheritDoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $channel = $this->contextMediator->getChannel($context);
        if (!$channel) {
            throw new InvalidArgumentException('MagentoOrderReader must have initialized channel!');
        }

        $marelloOrderIds = $context->getOption('ids', []);

        $orderIdentifierDTOs = [];
        if (!empty($marelloOrderIds)) {
            $orderIdentifierDTOs = $this->orderRepository->getOrdersIdentifierDTOsByChannelAndOrderIds(
                $channel,
                $marelloOrderIds
            );
        }

        $this->setSourceIterator(new \ArrayIterator($orderIdentifierDTOs));
    }
}
