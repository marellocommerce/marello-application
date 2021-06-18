<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\ProductBundle\Entity\Builder\ProductFamilyBuilder;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;

use Marello\Bundle\ProductBundle\Entity\Product;

class UpdateExistingProductsWithAttributeFamily extends AbstractFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadDefaultProductFamilyData::class
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $products = $manager
            ->getRepository(Product::class)
            ->findBy(['attributeFamily' => null]);

        if (count($products) === 0) {
            return;
        }

        /** @var AttributeFamily $attributeFamily */
        $attributeFamily = $this->getReference(ProductFamilyBuilder::DEFAULT_FAMILY_CODE);
        foreach ($products as $product) {
            $product->setAttributeFamily($attributeFamily);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
