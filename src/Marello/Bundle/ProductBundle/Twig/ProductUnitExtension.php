<?php

namespace Marello\Bundle\ProductBundle\Twig;

use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

use Marello\Bundle\ProductBundle\Migrations\Data\ORM\LoadProductUnitData;

class ProductUnitExtension extends AbstractExtension
{
    const NAME = 'marello_product_unit_value_formatter';

    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /**
     * ProductUnitExtension constructor.
     *
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
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
                'get_product_unit_value_by_id',
                [$this, 'getProductUnitValueById']
            )
        ];
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'marello_format_product_unit',
                [$this, 'formatProductUnit']
            )
        ];
    }

    /**
     * @param string $id
     * @return null|string
     */
    public function getProductUnitValueById($id)
    {
        if (!$id) {
            return null;
        }

        $productUnitClass = ExtendHelper::buildEnumValueClassName(LoadProductUnitData::PRODUCT_UNIT_ENUM_CLASS);
        $productUnit = $this->doctrineHelper
            ->getEntityRepositoryForClass($productUnitClass)
            ->find($id);

        if ($productUnit) {
            return $productUnit->getName();
        }

        return null;
    }

    /**
     * @param $productUnit
     * @return null|string
     */
    public function formatProductUnit($productUnit)
    {
        if ($productUnit instanceof AbstractEnumValue) {
            return $productUnit->getName();
        }

        return null;
    }
}
