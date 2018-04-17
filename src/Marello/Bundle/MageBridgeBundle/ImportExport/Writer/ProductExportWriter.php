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

            //TODO: Fix me
            $this->magentoResourceOwner->getWebsites(json_encode($item));
            die(__METHOD__ .'####'. __LINE__);


            $this->magentoResourceOwner->createProduct(json_encode($item));
        }
    }
}
