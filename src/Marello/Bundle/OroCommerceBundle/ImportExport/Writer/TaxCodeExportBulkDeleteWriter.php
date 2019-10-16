<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\TaxBundle\Entity\TaxCode;

class TaxCodeExportBulkDeleteWriter extends AbstractBulkExportWriter
{
    /**
     * {@inheritdoc}
     */
    protected function writeItems(array $entities)
    {
        $ids = [];
        $localIds = [];
        $channelId = $this->channel->getId();
        /** @var TaxCode[] $entities */
        foreach ($entities as $entity) {
            $data = $entity->getData();
            if (isset($data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID]) &&
                isset($data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId])) {
                $ids[] = $data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId];
                $localIds[] = $entity->getId();
            }
        }
        if (!empty($ids)) {
            $response = $this->transport->bulkDeleteProductTaxCodes($ids);
            if ($response->getStatusCode() === 204) {
                $em = $this->registry->getManagerForClass(TaxCode::class);
                $processedEntities = $em->getRepository(TaxCode::class)->findBy(['id' => $localIds]);
                foreach ($processedEntities as $entity) {
                    $data = $entity->getData();
                    unset($data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID][$channelId]);
                    if (empty($data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID])) {
                        unset($data[TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID]);
                    }
                    $entity->setData($data);
                    $em->persist($entity);
                }
                $this->context->incrementDeleteCount(count($ids));
            }
        }
    }
}
