<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

class TaxJurisdictionExportUpdateWriter extends AbstractProductExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->updateTaxJurisdiction($data);
        if (isset($response['data']) &&
            isset($response['data']['type']) && $response['data']['type'] === 'taxjurisdictions') {
            $this->context->incrementUpdateCount();
        }
    }
}
