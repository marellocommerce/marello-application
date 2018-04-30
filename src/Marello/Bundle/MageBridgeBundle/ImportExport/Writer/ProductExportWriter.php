<?php

namespace Marello\Bundle\MageBridgeBundle\ImportExport\Writer;

class ProductExportWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->initTransport();

        foreach ($items as $item) {
            $this->magentoResourceOwner->createProduct(json_encode($item));
        }
    }
}
