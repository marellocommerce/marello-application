<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Reader;

use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\IteratorBasedReader;

class TaxExportDeleteReader extends IteratorBasedReader
{
    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($context->getOption(AbstractExportWriter::ACTION_FIELD) ===
            AbstractExportWriter::DELETE_ACTION) {
            if ($context->hasOption(ProductExportUpdateReader::ID_FILTER)) {
                $array = [
                    [
                        ProductExportUpdateReader::ID_FILTER =>
                            $context->getOption(ProductExportUpdateReader::ID_FILTER)
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
