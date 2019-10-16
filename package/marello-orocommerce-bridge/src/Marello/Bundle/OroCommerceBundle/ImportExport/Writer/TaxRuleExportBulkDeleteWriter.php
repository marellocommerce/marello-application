<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Writer;

use Marello\Bundle\TaxBundle\Entity\TaxRule;

class TaxRuleExportBulkDeleteWriter extends AbstractBulkExportWriter
{
    /**
     * {@inheritdoc}
     */
    protected function writeItems(array $entities)
    {
        $ids = [];
        $localIds = [];
        $channelId = $this->channel->getId();
        /** @var TaxRule[] $entities */
        foreach ($entities as $entity) {
            $data = $entity->getData();
            if (isset($data[TaxRuleExportCreateWriter::TAX_RULE_ID]) &&
                isset($data[TaxRuleExportCreateWriter::TAX_RULE_ID][$channelId])) {
                $ids[] = $data[TaxRuleExportCreateWriter::TAX_RULE_ID][$channelId];
                $localIds[] = $entity->getId();
            }
        }
        if (!empty($ids)) {
            $response = $this->transport->bulkDeleteTaxRules($ids);
            if ($response->getStatusCode() === 204) {
                $em = $this->registry->getManagerForClass(TaxRule::class);
                $processedEntities = $em->getRepository(TaxRule::class)->findBy(['id' => $localIds]);
                foreach ($processedEntities as $entity) {
                    $data = $entity->getData();
                    unset($data[TaxRuleExportCreateWriter::TAX_RULE_ID][$channelId]);
                    if (empty($data[TaxRuleExportCreateWriter::TAX_RULE_ID])) {
                        unset($data[TaxRuleExportCreateWriter::TAX_RULE_ID]);
                    }
                    $entity->setData($data);
                    $em->persist($entity);
                }
                $this->context->incrementDeleteCount(count($ids));
            }
        }
    }
}
