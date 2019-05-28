<?php

namespace Marello\Bundle\ProductBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Provider\ProductTypesProvider;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;

class ProductAttributeFamilyEventListener
{
    /**
     * @var ProductTypesProvider
     */
    private $productTypesProvider;

    /**
     * @param ProductTypesProvider $productTypesProvider
     */
    public function __construct(ProductTypesProvider $productTypesProvider)
    {
        $this->productTypesProvider = $productTypesProvider;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();
        if ($entity instanceof Product && $entity->getType() && !$entity->getAttributeFamily()) {
            $productType = $this->productTypesProvider->getProductType($entity->getType());
            if ($productType && $productType->getAttributeFamilyCode()) {
                $attributeFamily = $em
                    ->getRepository(AttributeFamily::class)
                    ->findOneBy(['code' => $productType->getAttributeFamilyCode()]);
                if ($attributeFamily) {
                    $entity->setAttributeFamily($attributeFamily);
                }
            }
        }
    }
}
