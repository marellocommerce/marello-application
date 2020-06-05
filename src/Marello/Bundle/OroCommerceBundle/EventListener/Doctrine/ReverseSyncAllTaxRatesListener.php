<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxRateExportBulkDeleteWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxRateExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\TaxBundle\Entity\TaxRate;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class ReverseSyncAllTaxRatesListener
{
    /**
     * @var TaxRateExportBulkDeleteWriter
     */
    protected $taxRatesBulkDeleteWriter;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param TaxRateExportBulkDeleteWriter $taxRatesBulkDeleteWriter
     */
    public function __construct(TaxRateExportBulkDeleteWriter $taxRatesBulkDeleteWriter)
    {
        $this->taxRatesBulkDeleteWriter = $taxRatesBulkDeleteWriter;
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
                $taxRates = $this->getSynchronizedTaxRates();
                $context = new Context(['channel' => $channelId]);
                $this->taxRatesBulkDeleteWriter->setImportExportContext($context);
                $this->taxRatesBulkDeleteWriter->write($taxRates);
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
            if (true === $transport->isDeleteRemoteDataOnDeletion()) {
                $this->entityManager = $args->getEntityManager();
                $taxRates = $this->getSynchronizedTaxRates();
                $context = new Context(['channel' => $channel->getId()]);
                $this->taxRatesBulkDeleteWriter->setImportExportContext($context);
                $this->taxRatesBulkDeleteWriter->write($taxRates);
            }
        }
    }

    /**
     * @return TaxRule[]
     */
    private function getSynchronizedTaxRates()
    {
        return $this->entityManager
            ->getRepository(TaxRate::class)
            ->findByDataKey(TaxRateExportCreateWriter::TAX_ID);
    }
}
