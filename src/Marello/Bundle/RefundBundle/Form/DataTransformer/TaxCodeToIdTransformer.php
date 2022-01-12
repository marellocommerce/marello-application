<?php

namespace Marello\Bundle\RefundBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FormBundle\Form\DataTransformer\EntityToIdTransformer;

class TaxCodeToIdTransformer extends EntityToIdTransformer
{
    /**
     * TaxCodeToIdTransformer constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct(
            $em,
            'MarelloTaxBundle:TaxCode',
            'id',
            function (EntityRepository $repository, $id) {
                $qb = $repository->createQueryBuilder('tc');

                return $qb
                    ->where(
                        $qb->expr()->eq('tc.id', $id)
                    );
            }
        );
    }
}
