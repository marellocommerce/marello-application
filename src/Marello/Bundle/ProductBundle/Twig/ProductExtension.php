<?php

namespace Marello\Bundle\ProductBundle\Twig;

use Marello\Bundle\CatalogBundle\Provider\CategoriesIdsProvider;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductExtension extends AbstractExtension
{
    const NAME = 'marello_product';
    
    /**
     * @var ChannelProvider
     */
    protected $channelProvider;

    /**
     * @var CategoriesIdsProvider
     */
    protected $categoriesIdsProvider;

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
            new TwigFunction(
                'marello_sales_get_saleschannel_ids',
                [$this, 'getSalesChannelsIds']
            ),
            new TwigFunction(
                'marello_product_get_categories_ids',
                [$this, 'getCategoriesIds']
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
}
