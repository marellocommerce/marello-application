<?php

namespace Marello\Bundle\CatalogBundle\Provider;

use Marello\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class CategoriesChoicesProvider implements CategoriesChoicesProviderInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories()
    {
        return array_reduce(
            $this->doctrineHelper
                ->getEntityManagerForClass(Category::class)
                ->getRepository(Category::class)
                ->findAll(),
            function (array $result, Category $category) {
                $label = $category->getName();
                $result[$label] = $category->getCode();

                return $result;
            },
            []
        );
    }
}
