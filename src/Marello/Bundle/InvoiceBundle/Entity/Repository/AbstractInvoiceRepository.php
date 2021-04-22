<?php

namespace Marello\Bundle\InvoiceBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\PaymentBundle\Entity\Payment;

class AbstractInvoiceRepository extends EntityRepository
{
    /**
     * @param Payment $payment
     */
    public function findOneByPayment(Payment $payment)
    {
        $qb = $this->createQueryBuilder('i');
        $qb
            ->where(
                $qb->expr()->isMemberOf(':payment', 'i.payments')
            )
            ->setParameter('payment', $payment->getId());

        return $qb->getQuery()->getOneOrNullResult();
    }
}
