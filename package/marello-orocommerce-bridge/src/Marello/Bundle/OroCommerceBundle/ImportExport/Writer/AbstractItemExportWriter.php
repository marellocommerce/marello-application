<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

abstract class AbstractItemExportWriter extends AbstractExportWriter
{
    /**
     * @param array $entities
     * @throws \Exception
     */
    public function write(array $entities)
    {
        $this->transport->init($this->getChannel()->getTransport());

        foreach ($entities as $entity) {
            $this->writeItem($entity);
        }
    }

    /**
     * @param array $data
     */
    abstract protected function writeItem(array $data);
}
