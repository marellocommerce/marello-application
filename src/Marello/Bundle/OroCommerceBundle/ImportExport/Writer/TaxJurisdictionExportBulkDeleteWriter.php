<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;

class TaxJurisdictionExportBulkDeleteWriter extends AbstractBulkExportWriter
{
    /**
     * {@inheritdoc}
     */
    protected function writeItems(array $entities)
    {
        $ids = [];
        $localIds = [];
        $channelId = $this->channel->getId();
        /** @var TaxJurisdiction[] $entities */
        foreach ($entities as $entity) {
            $data = $entity->getData();
            if (isset($data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID]) &&
                isset($data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId])) {
                $ids[] = $data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId];
                $localIds[] = $entity->getId();
            }
        }
        if (!empty($ids)) {
            $response = $this->transport->bulkDeleteTaxJurisdictions($ids);
            if ($response->getStatusCode() === 204) {
                $em = $this->registry->getManagerForClass(TaxJurisdiction::class);
                $processedEntities = $em->getRepository(TaxJurisdiction::class)->findBy(['id' => $localIds]);
                foreach ($processedEntities as $entity) {
                    $data = $entity->getData();
                    unset($data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID][$channelId]);
                    if (empty($data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID])) {
                        unset($data[TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID]);
                    }
                    $entity->setData($data);
                    $em->persist($entity);
                }
                $this->context->incrementDeleteCount(count($ids));
            }
        }
    }
}
