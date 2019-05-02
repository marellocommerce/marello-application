<?php

namespace Marello\Bundle\OrderBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;

class ProductToSkuTransformer extends EntityToIdTransformer
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
            'MarelloProductBundle:Product',
            'sku',
            function (EntityRepository $repository, $sku) {
                $qb = $repository->createQueryBuilder('p');

                return $qb
                    ->where(
                        $qb->expr()->like(
                            'p.sku',
                            $qb->expr()->literal($sku)
                        )
                    );
            }
        );
    }
}
