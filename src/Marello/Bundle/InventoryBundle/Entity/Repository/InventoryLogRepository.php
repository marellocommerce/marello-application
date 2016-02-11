<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\ProductBundle\Entity\Product;

class InventoryLogRepository extends EntityRepository
{
    /**
     * @param callable $queryModifier
     *
     * @return array
     */
    protected function execute(callable $queryModifier)
    {
        $qb = $this->createQueryBuilder('l');

        call_user_func($queryModifier, $qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Product|int $product
     * @param \DateTime   $from
     * @param \DateTime   $to
     *
     * @return array
     */
    public function findByProductAndPeriod($product, \DateTime $from, \DateTime $to)
    {
        return $this->execute(function (QueryBuilder $qb) use ($product, $from, $to) {
            $this->addProductConstraint($qb, $product);
            $this->addPeriodConstraint($qb, $from, $to);
            $qb->orderBy($qb->expr()->asc('l.createdAt'));
        });
    }

    public function findLastChangeForProductAndWarehouse($product, $warehouse)
    {
        $qb = $this->createQueryBuilder('l');

        $this->addProductConstraint($qb, $product);
        $this->addWarehouseConstraint($qb, $warehouse);

        $qb->orderBy($qb->expr()->desc('l.createdAt'))
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();

        if (empty($result)) {
            return null;
        }

        return reset($result);
    }

    /**
     * @param QueryBuilder $qb
     * @param Product|int  $product
     */
    protected function addProductConstraint(QueryBuilder $qb, $product)
    {
        if ($product instanceof Product) {
            $product = $product->getId();
        }

        $qb
            ->join('l.inventoryItem', 'ii')
            ->andWhere($qb->expr()->eq('IDENTITY(ii.product)', $qb->expr()->literal($product)));
    }

    /**
     * @param QueryBuilder $qb
     * @param \DateTime    $from
     * @param \DateTime    $to
     */
    protected function addPeriodConstraint(QueryBuilder $qb, \DateTime $from, \DateTime $to)
    {
        $qb
            ->andWhere($qb->expr()->between('l.createdAt', ':from', ':to'))
            ->setParameters([
                'from' => $from,
                'to'   => $to,
            ]);
    }

    private function addWarehouseConstraint(QueryBuilder $qb, $warehouse)
    {
        if ($warehouse instanceof Warehouse) {
            $warehouse = $warehouse->getId();
        }

        $qb
            ->andWhere($qb->expr()->eq('IDENTITY(ii.warehouse)', $qb->expr()->literal($warehouse)));
    }
}
