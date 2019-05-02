<?php

namespace Marello\Bundle\CatalogBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\CatalogBundle\Entity\Category;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;

class LoadCategoryData extends AbstractFixture implements DependentFixtureInterface
{
    const CATEGORY_1_REF = 'category1';
    const CATEGORY_2_REF = 'category2';
    const CATEGORY_3_REF = 'category3';

    /** @var ObjectManager $manager */
    protected $manager;

    /** @var \Oro\Bundle\OrganizationBundle\Entity\Organization $defaultOrganization  */
    protected $defaultOrganization;

    protected $data = [
        self::CATEGORY_1_REF => [
            'name'          => 'category with multiple products',
            'code'          => 'category_1',
            'products'      => [
                LoadProductData::PRODUCT_1_REF,
                LoadProductData::PRODUCT_2_REF
            ]
        ],
        self::CATEGORY_2_REF => [
            'name'          => 'category with a single product',
            'code'          => 'category_2',
            'products'      => [
                LoadProductData::PRODUCT_3_REF
            ]
        ],
        self::CATEGORY_3_REF => [
            'name'          => 'category without products',
            'code'          => 'category_3'
        ]
    ];

    public function getDependencies()
    {
        return [
            LoadProductData::class,
        ];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $organizations = $this->manager
            ->getRepository('OroOrganizationBundle:Organization')
            ->findAll();

        if (is_array($organizations) && count($organizations) > 0) {
            $this->defaultOrganization = array_shift($organizations);
        }

        $this->loadCategories();
    }

    /**
     * load categories
     */
    public function loadCategories()
    {
        foreach ($this->data as $categoryRef => $data) {
            $category = $this->createCategory($data);
            $this->setReference($categoryRef, $category);
        }
        $this->manager->flush();
    }

    /**
     * create new categories
     * @param array $data
     * @return Category $category
     */
    private function createCategory(array $data)
    {
        $category = new Category();
        $category->setName($data['name']);
        $category->setCode($data['code']);
        $category->setOrganization($this->defaultOrganization);

        if (isset($data['products'])) {
            foreach ($data['products'] as $productRef) {
                /** @var Product $product */
                $product = $this->getReference($productRef);
                $category->addProduct($product);
            }
        }

        $this->manager->persist($category);

        return $category;
    }
}
