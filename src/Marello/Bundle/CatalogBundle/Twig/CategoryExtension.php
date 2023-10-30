<?php

namespace Marello\Bundle\CatalogBundle\Twig;

use Doctrine\Persistence\ManagerRegistry;
use Marello\Bundle\CatalogBundle\Entity\Category;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategoryExtension extends AbstractExtension
{
    const NAME = 'marello_category';

    public function __construct(
        protected ManagerRegistry $doctrine
    ) {
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'marello_get_category_name_by_code',
                [$this, 'getCategoryNameByCode']
            )
        ];
    }

    /**
     * @param string $code
     * @return string
     */
    public function getCategoryNameByCode($code)
    {
        $category = $this->doctrine
            ->getManagerForClass(Category::class)
            ->getRepository(Category::class)
            ->findOneBy(['code' => $code]);
        if ($category) {
            return $category->getName();
        }

        return $code;
    }
}
