<?php

namespace Marello\Bundle\PaymentTermBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\PaymentTermBundle\Entity\PaymentTermSettings;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class PaymentTermSettingsRepository extends EntityRepository
{
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
     * @return PaymentTermSettings[]
     */
    public function findWithEnabledChannel()
    {
        $qb = $this->createQueryBuilder('pts');

        $qb
            ->join('pts.channel', 'ch')
            ->where('ch.enabled = true')
            ->orderBy('pts.id');

        return $this->aclHelper->apply($qb)->getResult();
    }
}
