<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Reader;

use Doctrine\Common\Persistence\ManagerRegistry;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;

class TaxExportReader extends EntityReader
{
    const CODE_FILTER = 'code';

    /**
     * @var string
     * @deprecated will be removed in 2.0
     */
    protected $code;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var string
     */
    protected $action;

    /**
     * @param ContextRegistry $contextRegistry
     * @param ManagerRegistry $registry
     * @param OwnershipMetadataProviderInterface $ownershipMetadata
     * @param string $action
     * @param string $entityName
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        ManagerRegistry $registry,
        OwnershipMetadataProviderInterface $ownershipMetadata,
        $action,
        $entityName
    ) {
        parent::__construct($contextRegistry, $registry, $ownershipMetadata);

        $this->action = $action;
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);

        $qb
            ->andWhere('o.' . self::CODE_FILTER . ' = :code')
            ->setParameter(self::CODE_FILTER, $this->getParametersFromContext(self::CODE_FILTER));

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
        if ($context->getOption('entityName') === $this->entityName) {
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
        if ($context->getOption('entityName') === $this->entityName) {
            if ($context->getOption(AbstractExportWriter::ACTION_FIELD) === $this->action) {
                $this->code = $context->getOption(self::CODE_FILTER);
            }
        }
        parent::initializeFromContext($context);
    }
}
