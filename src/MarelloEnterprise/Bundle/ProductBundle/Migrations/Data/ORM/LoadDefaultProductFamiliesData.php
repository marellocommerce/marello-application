<?php

namespace MarelloEnterprise\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\Query\Expr\Join;
use Marello\Bundle\ProductBundle\Entity\Builder\ProductFamilyBuilder;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Add default product family for organizations that does not have any product families
 */
class LoadDefaultProductFamiliesData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organizations = $this->getOrganizations($manager);

        /** @var ProductFamilyBuilder $productFamilyBuilder */
        $productFamilyBuilder = $this->container->get('marello_product.entity.builder.product_family');
        /** @var Organization $organization */
        foreach ($organizations as $organization) {
            $defaultProductFamily = $productFamilyBuilder
                ->createDefaultFamily($organization)
                ->addDefaultAttributeGroups()
                ->getFamily();

            $manager->persist($defaultProductFamily);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @return Organization[]
     */
    protected function getOrganizations(ObjectManager $manager)
    {
        $queryBuilder = $manager->getRepository(Organization::class)
            ->createQueryBuilder('org');

        return $queryBuilder
            ->leftJoin(AttributeFamily::class, 'family', Join::WITH, 'org.id = family.owner')
            ->where($queryBuilder->expr()->isNull('family.owner'))
            ->getQuery()
            ->getResult();
    }
}
