<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Reader;

use Marello\Bundle\Magento2Bundle\ImportExport\Strategy\DefaultMagento2ImportStrategy;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class OriginIdsContextReader extends AbstractReader implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var \ArrayIterator */
    protected $innerIterator;

    /**
     * {@inheritDoc}
     */
    public function read()
    {
        if (null === $this->innerIterator) {
            $this->logger->notice(
                '[Magento 2] The OriginIdsContextReader hasn\'t configured properly. '.
                'Expected innerIterator set, but got null.'
            );

            return null;
        }

        $result = null;
        if ($this->innerIterator->valid()) {
            $result  = $this->innerIterator->current();
            $context = $this->getContext();
            $context->incrementReadOffset();
            $context->incrementReadCount();
            $this->innerIterator->next();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $originIds = $this->getDataByContextKey(
            DefaultMagento2ImportStrategy::CONTEXT_ORIGIN_IDS_OF_IMPORTED_RECORDS
        );

        if (!empty($originIds) && \is_array($originIds)) {
            $this->innerIterator = new \ArrayIterator([$originIds]);
        } else {
            $this->innerIterator = new \ArrayIterator([]);
        }
    }

    /**
     * @param string $contextKey
     * @return mixed|null
     */
    protected function getDataByContextKey(string $contextKey)
    {
        $executionContext = $this->getStepExecution()->getJobExecution()->getExecutionContext();
        return $executionContext->get($contextKey);
    }
}
