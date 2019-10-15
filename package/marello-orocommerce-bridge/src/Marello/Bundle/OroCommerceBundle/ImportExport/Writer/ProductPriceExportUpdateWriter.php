<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

class ProductPriceExportUpdateWriter extends AbstractProductExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->updateProductPrice($data);
        if (isset($response['data']) &&
            isset($response['data']['type']) && $response['data']['type'] === 'productprices') {
            $this->context->incrementUpdateCount();
        }
    }
}
