<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\MakeProductAttributesTrait;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeGroup;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeGroupRelation;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class LoadProductSubscriptionAttributesAndGroupData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use MakeProductAttributesTrait;

    /** @var string */
    const GROUP_CODE = 'subscription';

    /** @var array */
    private $fields = [
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
        $this->makeProductAttributes($this->fields);
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

        $attributeGroup = new AttributeGroup();
        $attributeGroup->setAttributeFamily($subscriptionFamily);
        $attributeGroup->setDefaultLabel('Subscription');
        $attributeGroup->setCode(self::GROUP_CODE);
        $attributeGroup->setIsVisible(true);

        $configManager = $this->getConfigManager();
        foreach ($this->fields as $attribute => $data) {
            $fieldConfigModel = $configManager->getConfigFieldModel(Product::class, $attribute);
            $attributeGroupRelation = new AttributeGroupRelation();
            $attributeGroupRelation->setEntityConfigFieldId($fieldConfigModel->getId());
            $attributeGroup->addAttributeRelation($attributeGroupRelation);
        }

        $manager->persist($attributeGroup);
        $manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies()
    {
        return [
            LoadSubscriptionAttributeFamilyData::class
        ];
    }
}
