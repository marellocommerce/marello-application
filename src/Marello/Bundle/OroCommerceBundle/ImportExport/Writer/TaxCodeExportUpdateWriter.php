<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

class TaxCodeExportUpdateWriter extends AbstractProductExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->updateProductTaxCode($data);
        if (isset($response['data']) &&
            isset($response['data']['type']) && $response['data']['type'] === 'producttaxcodes') {
            $this->context->incrementUpdateCount();
        }
    }
}
