<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeGroupRelation;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AssignAttributesToDefaultFamily extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use MakeProductAttributesTrait;

    /**
     * @var array
     */
    const ATTRIBUTES = [
        'sku' => [
            'searchable' => true,
            'filterable' => true,
            'filter_by' => 'exact_value',
            'sortable' => true,
        ],
        'names' => [
            'searchable' => true,
            'filterable' => true,
            'filter_by' => 'exact_value',
            'sortable' => true,
        ],
        'channels' => [
            'visible' => true
        ],
        'status' => [
            'visible' => true
        ],
        'prices' => [
            'visible' => true
        ],
        'channelPrices' => [
            'visible' => true
        ],
        'taxCode' => [
            'visible' => true
        ],
        'salesChannelTaxCodes' => [
            'visible' => true
        ],
        'weight' => [
            'visible' => true
        ],
        'manufacturingCode' => [
            'visible' => true
        ],
        'warranty' => [
            'visible' => true
        ],
        'suppliers' => [
            'visible' => true
        ],
        'categories' => [
            'visible' => true
        ],
        'image' => [
            'visible' => true
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->updateProductAttributes(self::ATTRIBUTES);
        $this->addGroup($manager);
    }
    
    /**
     * @param ObjectManager $manager
     */
    private function addGroup(ObjectManager $manager)
    {
        $attributeFamilyRepository = $manager->getRepository(AttributeFamily::class);

        $defaultFamily =
            $attributeFamilyRepository->findOneBy([
                'code' => LoadDefaultAttributeFamilyData::DEFAULT_FAMILY_CODE
            ]);

        $attributeGroup = $defaultFamily->getAttributeGroup(LoadDefaultAttributeFamilyData::GENERAL_GROUP_CODE);

        $configManager = $this->getConfigManager();
        foreach (self::ATTRIBUTES as $attribute => $data) {
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
            LoadDefaultAttributeFamilyData::class
        ];
    }
}
