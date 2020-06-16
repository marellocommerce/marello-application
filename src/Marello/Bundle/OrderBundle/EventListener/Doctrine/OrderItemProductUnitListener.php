<?php

namespace Marello\Bundle\OrderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadProductUnitData;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class OrderItemProductUnitListener
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        DoctrineHelper $doctrineHelper
    ) {
        $this->doctrineHelper = $doctrineHelper;
    }


    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof OrderItem && $entity->getProductUnit() === null) {
            $entity->setProductUnit($this->findDefaultProductUnit());
        }
    }

    /**
     * @return null|object
     */
    private function findDefaultProductUnit()
    {
        $productUnitClass = ExtendHelper::buildEnumValueClassName(LoadProductUnitData::PRODUCT_UNIT_ENUM_CLASS);
        $productUnit = $this->doctrineHelper
            ->getEntityManagerForClass($productUnitClass)
            ->getRepository($productUnitClass)
            ->findOneByDefault(true);

        if ($productUnit) {
            return $productUnit;
        }

        return null;
    }
}
