<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Reader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;

class InventoryLevelExportReader extends EntityReader
{
    const PRODUCT_FILTER = 'product';
    const GROUP_FILTER = 'group';

    /**
     * @var int
     * @deprecated will be removed in 2.0
     */
    protected $productId;

    /**
     * @var int
     * @deprecated will be removed in 2.0
     */
    protected $groupId;

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
            ->innerJoin('o.salesChannelGroup', 'g')
            ->andWhere('p.id = :productId')
            ->andWhere('g.id = :groupId')
            ->setParameter('productId', $this->getParametersFromContext(self::PRODUCT_FILTER))
            ->setParameter('groupId', $this->getParametersFromContext(self::GROUP_FILTER));

        return $qb;
    }

    /**
     * @param string $parameter
     * @return string|null
     */
    protected function getParametersFromContext($parameter)
    {
        $context = $this->getContext();
        if ($context->getOption('entityName') === VirtualInventoryLevel::class) {
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
     * @deprecated will be removed in 2.0
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($context->getOption('entityName') === VirtualInventoryLevel::class) {
            if ($context->getOption(AbstractExportWriter::ACTION_FIELD) === $this->action) {
                $this->productId = $context->getOption('product');
                $this->groupId = $context->getOption('group');
            }
        }
        parent::initializeFromContext($context);
    }
}
