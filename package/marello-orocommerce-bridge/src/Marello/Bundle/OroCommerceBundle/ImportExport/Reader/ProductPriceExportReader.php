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
    const CURRENCY_FILTER = 'currency';
    const VALUE_FILTER = 'value';

    /**
     * @var string
     * @deprecated will be removed in 2.0
     */
    protected $sku;

    /**
     * @var float
     * @deprecated will be removed in 2.0
     */
    protected $value;

    /**
     * @var string
     * @deprecated will be removed in 2.0
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
            ->setParameter(self::SKU_FILTER, $this->getParametersFromContext(self::SKU_FILTER))
            ->setParameter(self::VALUE_FILTER, $this->getParametersFromContext(self::VALUE_FILTER))
            ->setParameter(self::CURRENCY_FILTER, $this->getParametersFromContext(self::CURRENCY_FILTER));

        return $qb;
    }

    /**
     * {@inheritdoc}
     * @param string $parameter
     * @return string|null
     */
    protected function getParametersFromContext($parameter)
    {
        $context = $this->getContext();
        if (in_array($context->getOption('entityName'), [ProductPrice::class, ProductChannelPrice::class])) {
            if ($context->getOption(AbstractExportWriter::ACTION_FIELD) === $this->action
                && $context->hasOption($parameter)
            ) {
                return $context->getOption($parameter);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @deprecated will be removed in 2.0 in favour of the parent action
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
