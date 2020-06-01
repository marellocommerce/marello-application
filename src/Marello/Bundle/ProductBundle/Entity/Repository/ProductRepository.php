<?php

namespace Marello\Bundle\ProductBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ProductRepository extends EntityRepository
{
    const PGSQL_DRIVER = 'pdo_pgsql';
    const MYSQL_DRIVER = 'pdo_mysql';

    /**
     * @var string
     */
    private $databaseDriver;
    
    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @param AclHelper $aclHelper
     */
    public function setAclHelper(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }

    /**
     * @param string $databaseDriver
     */
    public function setDatabaseDriver($databaseDriver)
    {
        $this->databaseDriver = $databaseDriver;
    }

    /**
     * @param SalesChannel $salesChannel
     *
     * @return Product[]
     */
    public function findByChannel(SalesChannel $salesChannel)
    {
        $qb = $this->createQueryBuilder('product');
        $qb
            ->where(
                $qb->expr()->isMemberOf(':salesChannel', 'product.channels')
            )
            ->setParameter('salesChannel', $salesChannel->getId());

        return $this->aclHelper->apply($qb->getQuery())->getResult();
    }

    /**
     * @param array $salesChannelIds
     * @return int[]
     */
    public function getProductIdsBySalesChannelIds(array $salesChannelIds): array
    {
        if (empty($salesChannelIds)) {
            return [];
        }

        $subQb = $this->createQueryBuilder('innerProduct');
        $subQb
            ->select('1')
            ->innerJoin('innerProduct.channels', 'sc')
            ->where(
                $subQb->expr()->andX(
                    $subQb->expr()->in('sc.id', ':salesChannelIds'),
                    $subQb->expr()->eq('innerProduct.id', 'product.id')
                )
            );

        $qb = $this->createQueryBuilder('product');
        $qb
            ->select('product.id')
            ->where($qb->expr()->exists($subQb))
            ->setParameter('salesChannelIds', $salesChannelIds);

        $result = $this->aclHelper->apply($qb->getQuery())->getResult();

        return \array_column($result, 'id');
    }

    /**
     * Return products for specified price list and product IDs
     *
     * @param int $salesChannel
     * @param array $productIds
     *
     * @return Product[]
     */
    public function findBySalesChannel($salesChannel, array $productIds)
    {
        if (!$productIds) {
            return [];
        }

        $qb = $this->createQueryBuilder('product');
        $qb
            ->where(
                $qb->expr()->isMemberOf(':salesChannel', 'product.channels'),
                $qb->expr()->in('product.id', ':productIds')
            )
            ->setParameter('salesChannel', $salesChannel)
            ->setParameter('productIds', $productIds);

        return $this->aclHelper->apply($qb->getQuery())->getResult();
    }

    /**
     * @param string $sku
     *
     * @return null|Product
     */
    public function findOneBySku($sku)
    {
        $queryBuilder = $this->createQueryBuilder('product');

        $queryBuilder->andWhere('UPPER(product.sku) = :sku')
            ->setParameter('sku', strtoupper($sku));

        return $this->aclHelper->apply($queryBuilder->getQuery())->getOneOrNullResult();
    }

    /**
     * @param string $pattern
     *
     * @return string[]
     */
    public function findAllSkuByPattern($pattern)
    {
        $matchedSku = [];

        $results = $this
            ->createQueryBuilder('product')
            ->select('product.sku')
            ->where('product.sku LIKE :pattern')
            ->setParameter('pattern', $pattern)
            ->getQuery()
            ->getResult();

        foreach ($results as $result) {
            $matchedSku[] = $result['sku'];
        }

        return $matchedSku;
    }

    /**
     * @param string $key
     * @return Product[]
     */
    public function findByDataKey($key)
    {
        if ($this->databaseDriver === self::PGSQL_DRIVER) {
            $formattedDataField = 'CAST(p.data as TEXT)';
        } else {
            $formattedDataField = 'p.data';
        }
        $qb = $this->createQueryBuilder('p');
        $qb
            ->where(sprintf('%s LIKE :key', $formattedDataField))
            ->setParameter('key', '%' . $key . '%');

        return $this->aclHelper->apply($qb->getQuery())->getResult();
    }

    /**
     * @return string[]
     */
    public function getPurchaseOrderItemsCandidates()
    {
        $qb = $this
            ->createQueryBuilder('p')
            ->select(
                'sup.name AS supplier,
                p.sku,
                (i.desiredInventory - COALESCE(SUM(l.inventory - l.allocatedInventory), 0)) AS orderAmount,
                i.purchaseInventory'
            )
            ->innerJoin('p.preferredSupplier', 'sup')
            ->innerJoin('p.status', 's')
            ->innerJoin('p.inventoryItems', 'i')
            ->innerJoin('i.inventoryLevels', 'l')
            ->where("sup.name <> ''")
            ->andWhere("s.name = 'enabled'")
            ->andWhere("i.replenishment = 'never_out_of_stock'")
            ->groupBy('p.sku')
            ->having('SUM(l.inventory - l.allocatedInventory) < i.purchaseInventory');

        return $this->aclHelper->apply($qb->getQuery())->getResult();
    }
}
