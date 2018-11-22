<?php

namespace Marello\Bundle\ProductBundle\Twig;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;

class ProductExtension extends \Twig_Extension
{
    const NAME = 'marello_product';
    
    /**
     * @var ChannelProvider
     */
    protected $channelProvider;
    
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @param ChannelProvider $channelProvider
     * @param ProductRepository $productRepository
     */
    public function __construct(ChannelProvider $channelProvider, ProductRepository $productRepository)
    {
        $this->channelProvider = $channelProvider;
        $this->productRepository = $productRepository;
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
                'marello_get_products_names_by_ids',
                [$this, 'getProductsNamesByIds']
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
     * @param array $productIds
     * @return string
     */
    public function getProductsNamesByIds(array $productIds)
    {
        $products = $this->productRepository->findBy(['id' => $productIds]);
        $producsNames = implode(', ', array_map(function(Product $product){
            return $product->getName();
        }, $products));
        
        return $producsNames;
    }
}
