<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Reader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\PricingBundle\Entity\ProductChannelPrice;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;

class ProductPriceExportReader extends EntityReader
{
    const SKU_FILTER = 'sku';

    /**
     * @var string
     */
    protected $sku;

    /**
     * @var float
     */
    protected $value;

    /**
     * @var string
     */
    protected $currency;

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
            ->innerJoin('o.product', 'p')
            ->andWhere('p.' . self::SKU_FILTER . ' = :sku')
            ->andWhere('o.value = :value')
            ->andWhere('o.currency = :currency')
            ->setParameter(self::SKU_FILTER, $this->sku ? : -1)
            ->setParameter('value', $this->value ? : -1)
            ->setParameter('currency', $this->currency ? : -1);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (in_array($context->getOption('entityName'), [ProductPrice::class, ProductChannelPrice::class])) {
            if ($context->getOption(AbstractExportWriter::ACTION_FIELD) === $this->action) {
                $this->sku = $context->getOption(self::SKU_FILTER);
                $this->value = $context->getOption('value');
                $this->currency = $context->getOption('currency');
            }
        }
        parent::initializeFromContext($context);
    }
}
