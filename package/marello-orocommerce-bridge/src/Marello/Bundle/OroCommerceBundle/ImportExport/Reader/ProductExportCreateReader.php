<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Reader;

use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class ProductExportCreateReader extends EntityReader
{
    const SKU_FILTER = 'sku';

    /**
     * @var string
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
            ->setParameter('sku', $this->sku ? : -1);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($context->getOption(AbstractExportWriter::ACTION_FIELD) === AbstractExportWriter::CREATE_ACTION) {
            $this->sku = $context->getOption(self::SKU_FILTER);
            for ($i = 0; $i < 5; $i++) {
                $existing = $this->registry->getManagerForClass(Product::class)
                    ->getRepository(Product::class)
                    ->findOneBy(['sku' => $this->sku]);
                if ($existing) {
                    break;
                }
                sleep(5);
            }
        }

        parent::initializeFromContext($context);
    }
}
