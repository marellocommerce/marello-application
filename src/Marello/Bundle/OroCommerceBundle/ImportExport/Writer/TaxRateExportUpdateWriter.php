<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

class TaxRateExportUpdateWriter extends AbstractProductExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->updateTax($data);
        if (isset($response['data']) &&
            isset($response['data']['type']) && $response['data']['type'] === 'taxes') {
            $this->context->incrementUpdateCount();
        }
    }
}
