<?php

namespace MarelloEnterprise\Bundle\ProductBundle\EventListener\ORM;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Marello\Bundle\ProductBundle\Entity\Builder\ProductFamilyBuilder;

/**
 * Create Product Family for each new organization created
 */
class OrganizationListener
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var ProductFamilyBuilder
     */
    private $productFamilyBuilder;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ProductFamilyBuilder $productFamilyBuilder
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ProductFamilyBuilder $productFamilyBuilder
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->productFamilyBuilder = $productFamilyBuilder;
    }

    /**
     * @param Organization $organization
     */
    public function prePersist(Organization $organization): void
    {
        $defaultProductFamily = $this->productFamilyBuilder
            ->createDefaultFamily($organization)
            ->addDefaultAttributeGroups()
            ->getFamily();

        $manager = $this->doctrineHelper->getEntityManager(AttributeFamily::class);
        $manager->persist($defaultProductFamily);
    }
}
