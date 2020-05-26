<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;

class ProductExportDeleteRemovedReader extends IteratorBasedReader
{
    /**
     * {@inheritDoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $skus = $context->getOption('skus', []);
        $this->setSourceIterator(new \ArrayIterator($skus));
    }
}
