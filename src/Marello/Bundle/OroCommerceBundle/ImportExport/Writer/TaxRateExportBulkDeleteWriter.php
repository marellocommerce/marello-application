<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\TaxBundle\Entity\TaxRate;

class TaxRateExportBulkDeleteWriter extends AbstractBulkExportWriter
{
    /**
     * {@inheritdoc}
     */
    protected function writeItems(array $entities)
    {
        $ids = [];
        $localIds = [];
        $channelId = $this->channel->getId();
        /** @var TaxRate[] $entities */
        foreach ($entities as $entity) {
            $data = $entity->getData();
            if (isset($data[TaxRateExportCreateWriter::TAX_ID]) &&
                isset($data[TaxRateExportCreateWriter::TAX_ID][$channelId])) {
                $ids[] = $data[TaxRateExportCreateWriter::TAX_ID][$channelId];
                $localIds[] = $entity->getId();
            }
        }
        if (!empty($ids)) {
            $response = $this->transport->bulkDeleteTaxes($ids);
            if ($response->getStatusCode() === 204) {
                $em = $this->registry->getManagerForClass(TaxRate::class);
                $processedEntities = $em->getRepository(TaxRate::class)->findBy(['id' => $localIds]);
                foreach ($processedEntities as $entity) {
                    $data = $entity->getData();
                    unset($data[TaxRateExportCreateWriter::TAX_ID][$channelId]);
                    if (empty($data[TaxRateExportCreateWriter::TAX_ID])) {
                        unset($data[TaxRateExportCreateWriter::TAX_ID]);
                    }
                    $entity->setData($data);
                    $em->persist($entity);
                }
                $this->context->incrementDeleteCount(count($ids));
            }
        }
    }
}
