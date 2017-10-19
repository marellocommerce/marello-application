<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class WarehouseChannelGroupLinkRepository extends EntityRepository
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
     * @return WarehouseChannelGroupLink
     */
    public function findSystemLink()
    {
        $qb = $this->createQueryBuilder('wcgl');
        $qb
            ->where($qb->expr()->eq('wcgl.system', true));
        $results = $this->aclHelper->apply($qb)->getResult();

        return reset($results);
    }
}
