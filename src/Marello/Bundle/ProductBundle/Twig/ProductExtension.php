<?php

namespace Marello\Bundle\ProductBundle\Twig;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;

class ProductExtension extends \Twig_Extension
{
    const NAME = 'marello_product';
    
    /** @var ChannelProvider */
    protected $provider;

    /**
     * ProductExtension constructor.
     *
     * @param ChannelProvider $provider
     */
    public function __construct(ChannelProvider $provider)
    {
        $this->provider = $provider;
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
        ];
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    public function getSalesChannelsIds(Product $product)
    {
        return $this->provider->getSalesChannelsIds($product);
    }
}
