<?php

namespace Marello\Bundle\ProductBundle\Duplicator;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class SkuIncrementor implements SkuIncrementorInterface
{
    const INCREMENTED_SKU_PATTERN = '/^(.*)-Copy\d+$/';
    const SKU_INCREMENT_PATTERN = '/^%s-Copy(\d+)$/';
    const SKU_INCREMENT_DATABASE_PATTERN = '%s-%%';

    /**
     * @var string[]
     */
    protected $newSku = [];

    public function __construct(
        protected DoctrineHelper $doctrineHelper,
        protected string $productClass,
        protected AclHelper $aclHelper
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function increment($sku)
    {
        $maxIndex = 0;

        $sku = $this->defineBaseSku($sku);
        $possibleMatches = array_merge($this->getPreMatchedIncrementSku($sku), $this->newSku);

        foreach ($possibleMatches as $incrementedSku) {
            if (preg_match($this->buildSkuIncrementPattern($sku), $incrementedSku, $matches)) {
                $maxIndex = max($maxIndex, $matches[1]);
            }
        }

        $this->newSku[] = $newSku = sprintf('%s-Copy%d', $sku, ++$maxIndex);

        return $newSku;
    }

    /**
     * @return ProductRepository
     */
    protected function getRepository()
    {
        return $this->doctrineHelper->getEntityRepository($this->productClass);
    }

    /**
     * @param string $sku
     * @return string[]
     */
    protected function getPreMatchedIncrementSku($sku)
    {
        return $this->getRepository()->findAllSkuByPattern(sprintf(self::SKU_INCREMENT_DATABASE_PATTERN, $sku));
    }

    /**
     * @param string $sku
     * @return string
     */
    protected function defineBaseSku($sku)
    {
        if (preg_match(self::INCREMENTED_SKU_PATTERN, $sku, $matches)) {
            $baseSku = $matches[1];

            if ($this->getRepository()->findOneBySku($baseSku, $this->aclHelper)) {
                return $baseSku;
            }
        }

        return $sku;
    }

    /**
     * @param string $sku
     * @return string
     */
    protected function buildSkuIncrementPattern($sku)
    {
        return sprintf(self::SKU_INCREMENT_PATTERN, preg_quote($sku, '/'));
    }
}
