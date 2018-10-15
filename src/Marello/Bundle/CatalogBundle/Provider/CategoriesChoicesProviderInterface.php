<?php

namespace Marello\Bundle\CatalogBundle\Provider;

interface CategoriesChoicesProviderInterface
{
    /**
     * @return array
     */
    public function getCategories();
}
