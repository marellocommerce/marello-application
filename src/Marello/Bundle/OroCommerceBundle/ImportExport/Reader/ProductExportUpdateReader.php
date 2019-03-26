<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Reader;

use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Reader\EntityReaderById;

class ProductExportUpdateReader extends EntityReaderById
{
    /**
     * @param ContextInterface $context
     * @return array
     */
    protected function getIdsFromContext(ContextInterface $context)
    {
        $ids = $context->getOption('ids', []);
        if ($context->getOption(AbstractExportWriter::ACTION_FIELD) === AbstractExportWriter::UPDATE_ACTION) {
            if ($context->hasOption('id')) {
                $id = $context->getOption('id');
                if (is_array($id)) {
                    $ids = array_unique(array_merge($ids, $id));
                } else {
                    if (!in_array($id, $ids)) {
                        array_push($ids, $context->getOption('id'));
                    }
                }
            }
        }

        if (empty($ids)) {
            $ids[] = -1;
        }

        return $ids;
    }
}
