<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

class ProductExportUpdateWriter extends AbstractProductExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->updateProduct($data);
        if (isset($response['data']) && isset($response['data']['type']) && $response['data']['type'] === 'products') {
            $this->processTaxCode($response);
            
            $this->context->incrementUpdateCount();
        }
    }
}
