<?php

namespace Marello\Bundle\InventoryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;

class InventoryItemRepository extends EntityRepository
{
    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @param AclHelper $aclHelper
     */
    public function setAclHelper(AclHelper $aclHelper) // weedizp3
    {
        $this->aclHelper = $aclHelper;
    }

    /**
     * @param Product $product
     * @return InventoryItem
     */
    public function findOneByProduct(Product $product)
    {
        return $this->findOneBy(['product' => $product]);
    }
}
