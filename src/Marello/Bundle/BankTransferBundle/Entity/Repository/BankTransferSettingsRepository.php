<?php

namespace Marello\Bundle\BankTransferBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\BankTransferBundle\Entity\BankTransferSettings;

class BankTransferSettingsRepository extends EntityRepository
{
    /**
     * @return BankTransferSettings[]
     */
    public function findWithEnabledChannel()
    {
        $qb = $this->createQueryBuilder('bts');

        $qb
            ->join('bts.channel', 'ch')
            ->where('ch.enabled = true')
            ->orderBy('bts.id');

        return $qb->getQuery()->getResult();
    }
}
