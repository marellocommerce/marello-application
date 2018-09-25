<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;

class TaxRateExportDeleteWriter extends AbstractExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->deleteTax($data[ProductExportUpdateReader::ID_FILTER]);
        if ($response->getStatusCode() === 204) {
            $this->context->incrementDeleteCount();
        }
    }
}
