<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeGroupRelation;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Builder\ProductFamilyBuilder;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\MakeProductAttributesTrait;
use Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadDefaultProductFamilyData;

class AddAttributesToDefaultFamily extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface,
    VersionedFixtureInterface
{
    use MakeProductAttributesTrait;

    const ATTRIBUTES = [
        'barcode' => [
            'is_global' => true
        ]
    ];

    const DATAGRID_ATTRIBUTES = [
        'barcode' => [
            'is_visible' => 3
        ]
    ];

    const DEFAULT_ATTRIBUTE_GROUP = 'general';

    /** @var ObjectManager $objectManager */
    protected $objectManager;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;
        $this->updateProductAttributes(self::ATTRIBUTES);
        $this->updateProductAttributeDataGridOptions(self::DATAGRID_ATTRIBUTES);
        $this->addOrUpdateGroup();
    }

    /**
     * {@inheritdoc}
     */
    private function addOrUpdateGroup()
    {
        $attributeFamilyRepository = $this->objectManager->getRepository(AttributeFamily::class);
        $attributeFamilies =
            $attributeFamilyRepository->findBy([
                'code' => [
                    ProductFamilyBuilder::DEFAULT_FAMILY_CODE,
                ]
            ]);
        $configManager = $this->getConfigManager();
        /** @var AttributeFamily $attributeFamily */
        foreach ($attributeFamilies as $attributeFamily) {
            $defaultAttributeGroup = $attributeFamily->getAttributeGroup(self::DEFAULT_ATTRIBUTE_GROUP);
            $customAttributes = self::ATTRIBUTES;
            foreach ($customAttributes as $attribute => $data) {
                $fieldConfigModel = $configManager->getConfigFieldModel(Product::class, $attribute);
                $attributeGroupRelation = new AttributeGroupRelation();
                $attributeGroupRelation->setEntityConfigFieldId($fieldConfigModel->getId());
                $defaultAttributeGroup->addAttributeRelation($attributeGroupRelation);
            }

            $this->objectManager->persist($defaultAttributeGroup);
        }
        $this->objectManager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies()
    {
        return [
            LoadDefaultProductFamilyData::class
        ];
    }

    /**
     * @return string|void
     */
    public function getVersion()
    {
        return '1.0';
    }
}
