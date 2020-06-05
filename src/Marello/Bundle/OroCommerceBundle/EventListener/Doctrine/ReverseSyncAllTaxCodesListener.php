<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxCodeExportBulkDeleteWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxCodeExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\TaxBundle\Entity\TaxCode;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class ReverseSyncAllTaxCodesListener
{
    /**
     * @var TaxCodeExportBulkDeleteWriter
     */
    protected $taxCodesBulkDeleteWriter;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param TaxCodeExportBulkDeleteWriter $taxCodesBulkDeleteWriter
     */
    public function __construct(TaxCodeExportBulkDeleteWriter $taxCodesBulkDeleteWriter)
    {
        $this->taxCodesBulkDeleteWriter = $taxCodesBulkDeleteWriter;
    }

    /**
     * @param Channel $channel
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Channel $channel, PreUpdateEventArgs $args)
    {

        if ($channel->getType() === OroCommerceChannelType::TYPE) {
            /** @var OroCommerceSettings $transport */
            $transport = $channel->getTransport();
            $this->entityManager = $args->getEntityManager();
            $changeSet = $args->getEntityChangeSet();
            $channelId = $channel->getId();
            if (count($changeSet) > 0 &&
                isset($changeSet['enabled']) &&
                $changeSet['enabled'][1] === false &&
                true === $transport->isDeleteRemoteDataOnDeactivation()
            ) {
                $taxCodes = $this->getSynchronizedTaxCodes();
                $context = new Context(['channel' => $channelId]);
                $this->taxCodesBulkDeleteWriter->setImportExportContext($context);
                $this->taxCodesBulkDeleteWriter->write($taxCodes);
            }
        }
    }

    /**
     * @param Channel $channel
     * @param LifecycleEventArgs $args
     */
    public function preRemove(Channel $channel, LifecycleEventArgs $args)
    {
        if ($channel->getType() === OroCommerceChannelType::TYPE) {
            /** @var OroCommerceSettings $transport */
            $transport = $channel->getTransport();
            if (true === $transport->isDeleteRemoteDataOnDeactivation()) {
                $this->entityManager = $args->getEntityManager();
                $taxCodes = $this->getSynchronizedTaxCodes();
                $context = new Context(['channel' => $channel->getId()]);
                $this->taxCodesBulkDeleteWriter->setImportExportContext($context);
                $this->taxCodesBulkDeleteWriter->write($taxCodes);
            }
        }
    }

    /**
     * @return TaxRule[]
     */
    private function getSynchronizedTaxCodes()
    {
        return $this->entityManager
            ->getRepository(TaxCode::class)
            ->findByDataKey(TaxCodeExportCreateWriter::PRODUCT_TAX_CODE_ID);
    }
}
