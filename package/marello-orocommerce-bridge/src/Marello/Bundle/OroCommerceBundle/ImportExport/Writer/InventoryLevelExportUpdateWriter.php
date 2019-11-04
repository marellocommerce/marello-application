<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

class InventoryLevelExportUpdateWriter extends AbstractProductExportWriter
{
    /**
     * @param array $data
     */
    protected function writeItem(array $data)
    {
        $response = $this->transport->updateInventoryLevel($data);
        if (isset($response['data']) &&
            isset($response['data']['type']) && $response['data']['type'] === 'inventorylevels') {
            $this->context->incrementUpdateCount();
        }
    }
}
