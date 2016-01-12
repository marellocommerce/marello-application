<?php

namespace Marello\Bundle\ReturnBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;

class OrderToOrderNumberTransformer extends EntityToIdTransformer
{
    /**
     * ProductToSkuTransformer constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct(
            $em,
            'MarelloOrderBundle:Order',
            'sku',
            function (EntityRepository $repository, $orderNumber) {
                $qb = $repository->createQueryBuilder('o');

                return $qb
                    ->where(
                        $qb->expr()->like(
                            'o.orderNumber',
                            $qb->expr()->literal($orderNumber)
                        )
                    );
            }
        );
    }
}
