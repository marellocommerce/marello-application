<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\OroCommerceBundle\ImportExport\Reader\TaxRuleExportReader;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceTaxRuleConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\DependencyInjection\ServiceLink;

class ReverseSyncAllTaxRulesListener
{
    /**
     * @var ServiceLink
     */
    protected $syncScheduler;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param ServiceLink $syncScheduler
     */
    public function __construct(ServiceLink $syncScheduler)
    {
        $this->syncScheduler = $syncScheduler;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $channel = $args->getEntity();
        if ($channel instanceof Channel && $channel->getType() === OroCommerceChannelType::TYPE && $channel->isEnabled()) {
            $this->entityManager = $args->getEntityManager();
            $taxRules = $this->getAllTaxRules();
            foreach ($taxRules as $taxRule) {
                $this->syncScheduler->getService()->schedule(
                    $channel->getId(),
                    OroCommerceTaxRuleConnector::TYPE,
                    [
                        AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::CREATE_ACTION,
                        TaxRuleExportReader::TAXCODE_FILTER => $taxRule->getTaxCode()->getCode(),
                        TaxRuleExportReader::TAXRATE_FILTER => $taxRule->getTaxRate()->getCode(),
                        TaxRuleExportReader::TAXJURISDICTION_FILTER => $taxRule->getTaxJurisdiction()->getCode(),
                    ]
                );
            }
        }
    }

    /**
     * @return TaxRule[]
     */
    private function getAllTaxRules()
    {
        return $this->entityManager->getRepository(TaxRule::class)->findAll();
    }
}