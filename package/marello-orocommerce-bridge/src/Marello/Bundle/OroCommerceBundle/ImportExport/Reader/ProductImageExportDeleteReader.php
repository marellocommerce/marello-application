<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Reader;

use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;
use Oro\Bundle\IntegrationBundle\Reader\EntityReaderById;

class ProductImageExportDeleteReader extends IteratorBasedReader
{
    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($context->getOption(AbstractExportWriter::ACTION_FIELD) ===
            AbstractExportWriter::DELETE_ACTION) {
            if ($context->hasOption(EntityReaderById::ID_FILTER) &&
                $context->hasOption(ProductExportCreateReader::SKU_FILTER)) {
                $array = [
                    [
                        ProductExportCreateReader::SKU_FILTER =>
                            $context->getOption(ProductExportCreateReader::SKU_FILTER),
                        EntityReaderById::ID_FILTER =>
                            $context->getOption(EntityReaderById::ID_FILTER)
                    ]
                ];
            } else {
                $array = [];
            }
        } else {
            $array = [];
        }
        $obj = new \ArrayObject($array);
        $this->setSourceIterator($obj->getIterator());
    }
}
