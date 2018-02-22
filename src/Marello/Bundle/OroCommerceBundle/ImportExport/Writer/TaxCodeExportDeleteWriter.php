<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\ProductExportUpdateReader;

class TaxCodeExportDeleteWriter extends AbstractExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->deleteProductTaxCode($data[ProductExportUpdateReader::ID_FILTER]);
        if ($response->getStatusCode() === 204) {
            $this->context->incrementDeleteCount();
        }
    }
}
