<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\AssignAttributesToDefaultFamily;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadDefaultAttributeFamilyData;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\MakeProductAttributesTrait;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeGroup;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeGroupRelation;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class LoadProductSubscriptionAttributesAndGroupData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use MakeProductAttributesTrait;

    /** @var string */
    const GROUP_CODE = 'subscription';
    const GROUP_LABEL = 'Subscription';

    /** @var array */
    const ATTRIBUTES = [
        'subscriptionDuration' => [
            'visible' => true
        ],
        'number_of_deliveries' => [
            'visible' => true
        ],
        'paymentTerm' => [
            'visible' => true
        ],
        'specialPriceDuration' => [
            'visible' => true
        ],
    ];

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager)
    {
        $this->makeProductAttributes(self::ATTRIBUTES, ExtendScope::ORIGIN_CUSTOM);
        $this->addGroup($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addGroup(ObjectManager $manager)
    {
        $attributeFamilyRepository = $manager->getRepository(AttributeFamily::class);

        $subscriptionFamily =
            $attributeFamilyRepository->findOneBy([
                'code' => LoadSubscriptionAttributeFamilyData::SUBSCRIPTION_FAMILY_CODE
            ]);

        $manager->persist(
            $this->createGroup(
                $subscriptionFamily,
                LoadDefaultAttributeFamilyData::GENERAL_GROUP_CODE,
                LoadDefaultAttributeFamilyData::GENERAL_GROUP_LABEL,
                AssignAttributesToDefaultFamily::ATTRIBUTES
            )
        );
        $manager->persist(
            $this->createGroup(
                $subscriptionFamily,
                self::GROUP_CODE,
                self::GROUP_LABEL,
                self::ATTRIBUTES
            )
        );
        $manager->flush();
    }

    /**
     * @param AttributeFamily $family
     * @param string $code
     * @param string $label
     * @param array $attributes
     * @return AttributeGroup
     */
    private function createGroup($family, $code, $label, $attributes)
    {
        $attributeGroup = new AttributeGroup();
        $attributeGroup->setAttributeFamily($family);
        $attributeGroup->setDefaultLabel($label);
        $attributeGroup->setCode($code);
        $attributeGroup->setIsVisible(true);

        $configManager = $this->getConfigManager();
        foreach ($attributes as $attribute => $data) {
            $fieldConfigModel = $configManager->getConfigFieldModel(Product::class, $attribute);
            $attributeGroupRelation = new AttributeGroupRelation();
            $attributeGroupRelation->setEntityConfigFieldId($fieldConfigModel->getId());
            $attributeGroup->addAttributeRelation($attributeGroupRelation);
        }
        
        return $attributeGroup;
    }

    /**
     * @inheritdoc
     */
    public function getDependencies()
    {
        return [
            LoadDefaultAttributeFamilyData::class,
            AssignAttributesToDefaultFamily::class,
            LoadSubscriptionAttributeFamilyData::class
        ];
    }
}
