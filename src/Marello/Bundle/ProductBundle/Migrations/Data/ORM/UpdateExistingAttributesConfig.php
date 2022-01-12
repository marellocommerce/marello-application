<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class UpdateExistingAttributesConfig extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use MakeProductAttributesTrait;

    /**
     * @var array
     */
    const ATTRIBUTES = [
        'sku' => [
            'is_attribute' => true
        ],
        'names' => [
            'is_attribute' => true
        ],
        'channels' => [
            'is_attribute' => true
        ],
        'status' => [
            'is_attribute' => true
        ],
        'prices' => [
            'is_attribute' => true
        ],
        'channelPrices' => [
            'is_attribute' => true
        ],
        'taxCode' => [
            'is_attribute' => true
        ],
        'salesChannelTaxCodes' => [
            'is_attribute' => true
        ],
        'weight' => [
            'is_attribute' => true
        ],
        'manufacturingCode' => [
            'is_attribute' => true
        ],
        'warranty' => [
            'is_attribute' => true
        ],
        'suppliers' => [
            'is_attribute' => true
        ],
        'categories' => [
            'is_attribute' => true
        ],
        'image' => [
            'is_attribute' => true
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->updateProductAttributes(self::ATTRIBUTES);
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
}
