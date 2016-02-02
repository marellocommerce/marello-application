<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
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
}
