<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Marello\Bundle\OroCommerceBundle\Entity\OroCommerceSettings;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxJurisdictionExportBulkDeleteWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\TaxJurisdictionExportCreateWriter;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\TaxBundle\Entity\TaxJurisdiction;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class ReverseSyncAllTaxJurisdictionsListener
{
    /**
     * @var TaxJurisdictionExportBulkDeleteWriter
     */
    protected $taxJurisdictionsBulkDeleteWriter;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param TaxJurisdictionExportBulkDeleteWriter $taxJurisdictionsBulkDeleteWriter
     */
    public function __construct(TaxJurisdictionExportBulkDeleteWriter $taxJurisdictionsBulkDeleteWriter)
    {
        $this->taxJurisdictionsBulkDeleteWriter = $taxJurisdictionsBulkDeleteWriter;
    }

    /**
     * @param Channel $channel
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Channel $channel, PreUpdateEventArgs $args)
    {

        if ($channel->getType() === OroCommerceChannelType::TYPE) {
            $settingsBag = $channel->getTransport()->getSettingsBag();
            $this->entityManager = $args->getEntityManager();
            $changeSet = $args->getEntityChangeSet();
            $channelId = $channel->getId();
            if (count($changeSet) > 0 &&
                isset($changeSet['enabled']) &&
                $changeSet['enabled'][1] === false &&
                $settingsBag->get(OroCommerceSettings::DELETE_REMOTE_DATA_ON_DEACTIVATION) === true)
            {
                $taxJurisdictions = $this->getSynchronizedTaxJurisdictions();
                $context = new Context(['channel' => $channelId]);
                $this->taxJurisdictionsBulkDeleteWriter->setImportExportContext($context);
                $this->taxJurisdictionsBulkDeleteWriter->write($taxJurisdictions);
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
            $settingsBag = $channel->getTransport()->getSettingsBag();
            if ($settingsBag->get(OroCommerceSettings::DELETE_REMOTE_DATA_ON_DELETION) === true) {
                $this->entityManager = $args->getEntityManager();
                $taxJurisdictions = $this->getSynchronizedTaxJurisdictions();
                $context = new Context(['channel' => $channel->getId()]);
                $this->taxJurisdictionsBulkDeleteWriter->setImportExportContext($context);
                $this->taxJurisdictionsBulkDeleteWriter->write($taxJurisdictions);
            }
        }
    }

    /**
     * @return TaxRule[]
     */
    private function getSynchronizedTaxJurisdictions()
    {
        return $this->entityManager
            ->getRepository(TaxJurisdiction::class)
            ->findByDataKey(TaxJurisdictionExportCreateWriter::TAX_JURISDICTION_ID);
    }
}