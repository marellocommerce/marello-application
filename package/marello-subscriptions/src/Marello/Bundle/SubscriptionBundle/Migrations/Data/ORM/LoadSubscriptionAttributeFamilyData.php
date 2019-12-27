<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;

class LoadSubscriptionAttributeFamilyData extends AbstractFixture implements DependentFixtureInterface
{
    const SUBSCRIPTION_FAMILY_CODE = 'marello_subscription';

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadAdminUserData::class
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = $manager
            ->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $organization = $this->getOrganization($manager);
        $attributeFamily = new AttributeFamily();
        $attributeFamily->setCode(self::SUBSCRIPTION_FAMILY_CODE);
        $attributeFamily->setEntityClass(Product::class);
        $attributeFamily->setDefaultLabel('Subscription');
        $attributeFamily->setOwner($organization);

        $manager->persist($attributeFamily);
        $manager->flush();
        
        $this->setReference(self::SUBSCRIPTION_FAMILY_CODE, $attributeFamily);
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Organization|object
     */
    private function getOrganization(ObjectManager $manager)
    {
        if ($this->hasReference(LoadOrganizationAndBusinessUnitData::REFERENCE_DEFAULT_ORGANIZATION)) {
            return $this->getReference(LoadOrganizationAndBusinessUnitData::REFERENCE_DEFAULT_ORGANIZATION);
        } else {
            return $manager
                ->getRepository('OroOrganizationBundle:Organization')
                ->getFirst();
        }
    }
}
