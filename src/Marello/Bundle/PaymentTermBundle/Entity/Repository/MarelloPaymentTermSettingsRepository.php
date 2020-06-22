<?php

namespace Marello\Bundle\PaymentTermBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\PaymentTermBundle\Entity\MarelloPaymentTermSettings;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class MarelloPaymentTermSettingsRepository extends EntityRepository
{
    /**
     * @return MarelloPaymentTermSettings[]
     */
    public function findWithEnabledChannel()
    {
        $qb = $this->createQueryBuilder('pts');

        $qb
            ->join('pts.channel', 'ch')
            ->where('ch.enabled = true')
            ->orderBy('pts.id');

        return $qb->getQuery()->getResult();
    }
}
