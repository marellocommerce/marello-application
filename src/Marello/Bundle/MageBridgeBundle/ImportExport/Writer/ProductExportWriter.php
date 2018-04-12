<?php

namespace Marello\Bundle\MageBridgeBundle\ImportExport\Writer;


class ProductExportWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $context = $this->contextRegistry
            ->getByStepExecution($this->stepExecution);

        $transportId = $context->getValue('mage_transport_id');
        $channelId = $context->getValue('mage_channel_id');


        die();
    }
}
