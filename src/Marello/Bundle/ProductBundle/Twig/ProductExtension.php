<?php

namespace Marello\Bundle\ProductBundle\Twig;

use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;
use Marello\Bundle\CatalogBundle\Provider\CategoriesIdsProvider;

class ProductExtension extends \Twig_Extension
{
    const NAME = 'marello_product';
    
    /**
     * @var ChannelProvider $channelProvider
     */
    protected $channelProvider;

    /**
     * @var CategoriesIdsProvider $categoriesIdsProvider
     */
    protected $categoriesIdsProvider;

    /** @var DoctrineHelper $doctrineHelper */
    private $doctrineHelper;

    /**
     * @param ChannelProvider $channelProvider
     * @param CategoriesIdsProvider $categoriesIdsProvider
     */
    public function __construct(ChannelProvider $channelProvider, CategoriesIdsProvider $categoriesIdsProvider)
    {
        $this->channelProvider = $channelProvider;
        $this->categoriesIdsProvider = $categoriesIdsProvider;
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
            new \Twig_SimpleFunction(
                'marello_sales_get_saleschannel_ids',
                [$this, 'getSalesChannelsIds']
            ),
            new \Twig_SimpleFunction(
                'marello_product_get_categories_ids',
                [$this, 'getCategoriesIds']
            ),
            new \Twig_SimpleFunction(
                'marello_get_product_by_sku',
                [$this, 'getProductBySku']
            )
        ];
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    public function getSalesChannelsIds(Product $product)
    {
        return $this->channelProvider->getSalesChannelsIds($product);
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    public function getCategoriesIds(Product $product)
    {
        return $this->categoriesIdsProvider->getCategoriesIds($product);
    }

    /**
     * @param $sku
     * @return \Extend\Entity\EX_MarelloProductBundle_Product|Product|null
     */
    public function getProductBySku($sku)
    {
        if (!$this->doctrineHelper || !$sku) {
            return null;
        }
        /** @var ProductRepository $productRepository */
        $productRepository = $this->doctrineHelper->getEntityRepository(Product::class);
        return $productRepository->findOneBySku($sku);
    }

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function setOroEntityDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }
}
