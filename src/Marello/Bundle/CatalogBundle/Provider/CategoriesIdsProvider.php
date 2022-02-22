<?php

namespace Marello\Bundle\CatalogBundle\Provider;

use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\CatalogBundle\Entity\Category;
use Marello\Bundle\ProductBundle\Entity\Product;

class CategoriesIdsProvider
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Product $product
     *
     * @return array $ids
     */
    public function getCategoriesIds(Product $product)
    {
        $ids = [];
        $product
            ->getCategories()
            ->map(function (Category $category) use (&$ids) {
                $ids[] = $category->getId();
            });

        return $ids;
    }

    /**
     * @param Product $product
     *
     * @return array $ids
     */
    public function getExcludedCategoriesIds(Product $product)
    {
        $relatedIds = $this->getCategoriesIds($product);
        $excludedIds = [];

        $ids = $this->manager
            ->getRepository(Category::class)
            ->findExcludedCategoriesIds($relatedIds);

        foreach ($ids as $k => $v) {
            $excludedIds[] = $v['id'];
        }

        return $excludedIds;
    }
}
