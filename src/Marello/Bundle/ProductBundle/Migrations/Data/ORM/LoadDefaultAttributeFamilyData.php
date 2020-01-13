<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeGroup;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;

class LoadDefaultAttributeFamilyData extends AbstractFixture implements DependentFixtureInterface
{
    const DEFAULT_FAMILY_CODE = 'marello_default';
    const GENERAL_GROUP_CODE = 'general';
    const GENERAL_GROUP_LABEL = 'General';

    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        [
            'groupLabel' => self::GENERAL_GROUP_LABEL,
            'groupCode' => self::GENERAL_GROUP_CODE,
            'attributes' => [],
            'groupVisibility' => false
        ]
    ];

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
        $organization = $this->getOrganization($manager);
        $user = $organization->getUsers()->first();
        $attributeFamily = new AttributeFamily();
        $attributeFamily->setCode(self::DEFAULT_FAMILY_CODE);
        $attributeFamily->setEntityClass(Product::class);
        $attributeFamily->addLabel((new LocalizedFallbackValue())->setString('Default'));
        $attributeFamily->setOrganization($organization);
        $attributeFamily->setOwner($user);

        $this->setReference(self::DEFAULT_FAMILY_CODE, $attributeFamily);

        $this->addGroupsWithAttributesToFamily($this->data, $attributeFamily, $manager);
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

    /**
     *
     * @param array $groupsData
     * @param AttributeFamily $attributeFamily
     * @param ObjectManager $manager
     */
    protected function addGroupsWithAttributesToFamily(
        array $groupsData,
        AttributeFamily $attributeFamily,
        ObjectManager $manager
    ) {
        foreach ($groupsData as $groupData) {
            $attributeGroup = new AttributeGroup();
            $attributeGroup->addLabel(((new LocalizedFallbackValue())->setString($groupData['groupLabel'])));
            $attributeGroup->setIsVisible($groupData['groupVisibility']);
            $attributeGroup->setCode($groupData['groupCode']);
            $attributeFamily->addAttributeGroup($attributeGroup);
        }

        $manager->persist($attributeFamily);
        $manager->flush();
    }
}
