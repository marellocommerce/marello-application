<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

class ProductImageExportUpdateWriter extends AbstractProductExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->updateProductImage($data);
        if (isset($response['data']) &&
            isset($response['data']['type']) && $response['data']['type'] === 'productimages') {
            $this->context->incrementUpdateCount();
        }
    }
}
