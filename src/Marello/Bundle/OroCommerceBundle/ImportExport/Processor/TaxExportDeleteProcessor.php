<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Processor;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;

class TaxExportDeleteProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        if (!isset($data[ProductExportUpdateReader::ID_FILTER])) {
            throw new InvalidItemException('Invalid Item', $data);
        }
        
        return $data;
    }
}
