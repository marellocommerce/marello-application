<?php

namespace Marello\Bundle\ProductBundle\VirtualFields;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Model\ProductHolderInterface;
use Marello\Bundle\ProductBundle\VirtualFields\QueryDesigner\VirtualFieldsSelectQueryConverter;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class VirtualFieldsProductDecoratorFactory
{
    /**
     * @var EntityFieldProvider
     */
    private $entityFieldProvider;

    /**
     * @var VirtualFieldsSelectQueryConverter
     */
    private $converter;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @param EntityFieldProvider $entityFieldProvider
     * @param VirtualFieldsSelectQueryConverter $converter
     * @param ManagerRegistry $doctrine
     * @param FieldHelper $fieldHelper
     */
    public function __construct(
        EntityFieldProvider $entityFieldProvider,
        VirtualFieldsSelectQueryConverter $converter,
        ManagerRegistry $doctrine,
        FieldHelper $fieldHelper
    ) {
        $this->entityFieldProvider = $entityFieldProvider;
        $this->converter = $converter;
        $this->doctrine = $doctrine;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @param Product[] $products
     * @param Product $product
     *
     * @return VirtualFieldsProductDecorator
     */
    public function createDecoratedProduct(array $products, Product $product)
    {
        return new VirtualFieldsProductDecorator(
            $this->entityFieldProvider,
            $this->converter,
            $this->doctrine,
            $this->fieldHelper,
            $products,
            $product
        );
    }

    /**
     * @param ProductHolderInterface[] $productHolders
     * @param Product $product
     *
     * @return VirtualFieldsProductDecorator
     */
    public function createDecoratedProductByProductHolders(array $productHolders, Product $product)
    {
        $productHoldersProducts = array_map(
            function (ProductHolderInterface $productHolder) {
                return $productHolder->getProduct();
            },
            $productHolders
        );

        return $this->createDecoratedProduct($productHoldersProducts, $product);
    }
}
