<?php

namespace Marello\Bundle\ProductBundle\Twig;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Util\ProductHelper;

class ProductExtension extends \Twig_Extension
{
    const NAME = 'marello_product';
    
    /** @var ProductHelper */
    protected $productHelper;

    /**
     * ProductExtension constructor.
     *
     * @param ProductHelper $productHelper
     */
    public function __construct(ProductHelper $productHelper)
    {
        $this->productHelper = $productHelper;
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
                'marello_product_get_saleschannel_ids',
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
        return $this->productHelper->getSalesChannelsIds($product);
    }
}
