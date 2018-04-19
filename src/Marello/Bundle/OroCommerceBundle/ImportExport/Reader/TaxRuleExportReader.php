<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Reader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\TaxBundle\Entity\TaxRule;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;

class TaxRuleExportReader extends EntityReader
{
    const TAXCODE_FILTER = 'taxCode';
    const TAXRATE_FILTER = 'taxRate';
    const TAXJURISDICTION_FILTER = 'taxJurisdiction';

    /**
     * @var string
     */
    protected $taxCode;

    /**
     * @var float
     */
    protected $taxRate;

    /**
     * @var string
     */
    protected $taxJurisdiction;

    /**
     * @var string
     */
    protected $action;

    /**
     * @param ContextRegistry $contextRegistry
     * @param ManagerRegistry $registry
     * @param OwnershipMetadataProviderInterface $ownershipMetadata
     * @param string $action
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        ManagerRegistry $registry,
        OwnershipMetadataProviderInterface $ownershipMetadata,
        $action
    ) {
        parent::__construct($contextRegistry, $registry, $ownershipMetadata);

        $this->action = $action;
    }

    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);

        $qb
            ->innerJoin('o.taxCode', 'tc')
            ->innerJoin('o.taxRate', 'tr')
            ->innerJoin('o.taxJurisdiction', 'tj')
            ->andWhere('tc.code = :tc_code')
            ->andWhere('tr.code = :tr_code')
            ->andWhere('tj.code = :tj_code')
            ->setParameter('tc_code', $this->taxCode ? : -1)
            ->setParameter('tr_code', $this->taxRate ? : -1)
            ->setParameter('tj_code', $this->taxJurisdiction ? : -1);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($context->getOption('entityName') === TaxRule::class) {
            if ($context->getOption(AbstractExportWriter::ACTION_FIELD) === $this->action) {
                $this->taxCode = $context->getOption('taxCode');
                $this->taxRate = $context->getOption('taxRate');
                $this->taxJurisdiction = $context->getOption('taxJurisdiction');
            }
        }
        parent::initializeFromContext($context);
    }
}
