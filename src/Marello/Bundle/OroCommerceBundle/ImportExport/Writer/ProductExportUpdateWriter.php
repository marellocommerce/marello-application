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
            if (!isset($response['data'])) {
                $this->context->addError(
                    sprintf('Could not create product with data %s', $data['data']['attributes']['sku'])
                );
            }
        }

        if (isset($response['data']) && isset($response['data']['type']) && $response['data']['type'] === 'products') {
            $this->processTaxCode($response);
            
            $this->context->incrementUpdateCount();
        }
    }
}
