<?php

namespace Marello\Bundle\BankTransferBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\BankTransferBundle\Entity\BankTransferSettings;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class BankTransferSettingsRepository extends EntityRepository
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
     * @return BankTransferSettings[]
     */
    public function findWithEnabledChannel()
    {
        $qb = $this->createQueryBuilder('bts');

        $qb
            ->join('bts.channel', 'ch')
            ->where('ch.enabled = true')
            ->orderBy('bts.id');

        return $this->aclHelper->apply($qb)->getResult();
    }
}
