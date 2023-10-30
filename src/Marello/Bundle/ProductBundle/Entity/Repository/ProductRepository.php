<?php

namespace Marello\Bundle\ProductBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ProductRepository extends ServiceEntityRepository
{
    const PGSQL_DRIVER = 'pdo_pgsql';
    const MYSQL_DRIVER = 'pdo_mysql';

    /**
     * @var string
     */
    private $databaseDriver;

    /**
     * @param string $databaseDriver
     */
    public function setDatabaseDriver($databaseDriver)
    {
        $this->databaseDriver = $databaseDriver;
    }

    public function findByChannel(SalesChannel $salesChannel, AclHelper $aclHelper): array
    {
        $qb = $this->createQueryBuilder('product');
        $qb
            ->where(
                $qb->expr()->isMemberOf(':salesChannel', 'product.channels')
            )
            ->setParameter('salesChannel', $salesChannel->getId());

        return $aclHelper->apply($qb->getQuery())->getResult();
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getSalesChannelIdsByProductId(int $productId): array
    {
        $qb = $this->createQueryBuilder('product');
        $qb
            ->select('sc.id')
            ->innerJoin('product.channels', 'sc')
            ->where($qb->expr()->eq('product.id', ':productId'))
            ->setParameter('productId', $productId);

        $result = $qb->getQuery()->getArrayResult();

        return array_column($result, 'id');
    }

    public function getProductIdsBySalesChannelIds(array $salesChannelIds, AclHelper $aclHelper): array
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

        $result = $aclHelper->apply($qb->getQuery())->getResult();

        return \array_column($result, 'id');
    }

    /**
     * Return products for specified price list and product IDs
     */
    public function findBySalesChannel($salesChannel, array $productIds, AclHelper $aclHelper): array
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

        return $aclHelper->apply($qb->getQuery())->getResult();
    }

    public function findOneBySku($sku, AclHelper $aclHelper, Organization $organization = null)
    {
        $queryBuilder = $this->createQueryBuilder('product');

        $queryBuilder->andWhere('UPPER(product.sku) = :sku')
            ->setParameter('sku', strtoupper($sku));

        if ($organization) {
            $queryBuilder
                ->andWhere('product.organization = :organization')
                ->setParameter('organization', $organization);
        }

        return $aclHelper->apply($queryBuilder->getQuery())->getOneOrNullResult();
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

    public function findByDataKey(string $key, AclHelper $aclHelper): array
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

        return $aclHelper->apply($qb->getQuery())->getResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param string|null $productIdsToExclude
     * @return array
     */
    public function getPurchaseOrderItemsCandidates(AclHelper $aclHelper, string $productIdsToExclude = null): array
    {
        $qb = $this
            ->createQueryBuilder('p')
            ->select(
                'p.id,
                p.sku,
                sup.name AS supplier,
                SUM(i.desiredInventory - COALESCE((l.inventory - l.allocatedInventory), 0)) AS orderAmount,
                SUM(i.purchaseInventory) AS purchaseInventory'
            )
            ->innerJoin('p.preferredSupplier', 'sup')
            ->innerJoin('p.status', 's')
            ->innerJoin('p.inventoryItem', 'i')
            ->innerJoin('i.inventoryLevels', 'l')
            ->where("sup.name <> ''")
            ->andWhere("s.name = 'enabled'")
            ->andWhere("i.replenishment = 'never_out_of_stock'")
            ->groupBy('p.id, p.sku, sup.name, i.desiredInventory, i.purchaseInventory')
            ->having('SUM(l.inventory - l.allocatedInventory) < i.purchaseInventory');

        if (!empty($productIdsToExclude)) {
            $qb
                ->andWhere($qb->expr()->notIn('p.id', $productIdsToExclude));
        }

        return $aclHelper->apply($qb->getQuery())->getResult();
    }
}
