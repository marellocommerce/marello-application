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

        if (!isset($response['data'])) {
            $this->context->addError(sprintf('Could not update product with data %s', print_r($data, true)));
        }

        if (isset($response['data']) && isset($response['data']['type']) && $response['data']['type'] === 'products') {
            $this->processTaxCode($response);
            
            $this->context->incrementUpdateCount();
        }
    }
}
