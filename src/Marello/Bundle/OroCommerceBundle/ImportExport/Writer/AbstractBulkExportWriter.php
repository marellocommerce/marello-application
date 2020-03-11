<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

abstract class AbstractBulkExportWriter extends AbstractExportWriter
{
    /**
     * @param array $entities
     * @throws \Exception
     */
    public function write(array $entities)
    {
        $this->transport->init($this->getChannel()->getTransport());
        $this->writeItems($entities);
    }
    
    /**
     * @param array $entities
     */
    abstract protected function writeItems(array $entities);
}
