<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Reader;

use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class ProductExportCreateReader extends EntityReader
{
    const SKU_FILTER = 'sku';

    /**
     * @var string
     * @deprecated will be removed in 2.0
     */
    protected $sku;

    /**
     * {@inheritdoc}
     */
    protected function createSourceEntityQueryBuilder($entityName, Organization $organization = null, array $ids = [])
    {
        $qb = parent::createSourceEntityQueryBuilder($entityName, $organization, $ids);

        $qb
            ->andWhere('o.' . self::SKU_FILTER . ' = :sku')
            ->setParameter(self::SKU_FILTER, $this->getParametersFromContext(self::SKU_FILTER));

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
        if ($context->getOption(AbstractExportWriter::ACTION_FIELD) === AbstractExportWriter::CREATE_ACTION
            && $context->hasOption($parameter)
        ) {
            return $context->getOption($parameter);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($context->getOption(AbstractExportWriter::ACTION_FIELD) === AbstractExportWriter::CREATE_ACTION) {
            /** @var Channel $channel */
            $channel = $this->registry->getManagerForClass(Channel::class)
                ->getRepository(Channel::class)
                ->find($this->getParametersFromContext('channel'));
            if ($channel) {
                for ($i = 0; $i < 5; $i++) {
                    $existing = $this->registry->getManagerForClass(Product::class)
                        ->getRepository(Product::class)
                        ->findOneBy([
                            'sku' => $this->getParametersFromContext(self::SKU_FILTER),
                            'organization' => $channel->getOrganization()
                        ]);
                    if ($existing) {
                        break;
                    }
                    sleep(5);
                }
            }
        }

        parent::initializeFromContext($context);
    }
}
