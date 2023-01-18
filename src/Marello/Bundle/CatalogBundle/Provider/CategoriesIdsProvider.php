<?php

namespace Marello\Bundle\CatalogBundle\Provider;

use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\CatalogBundle\Entity\Category;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class CategoriesIdsProvider
{
    public function __construct(
        protected ObjectManager $manager,
        protected AclHelper $aclHelper
    ) {
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
            ->findExcludedCategoriesIds($relatedIds, $this->aclHelper);

        foreach ($ids as $k => $v) {
            $excludedIds[] = $v['id'];
        }

        return $excludedIds;
    }
}
