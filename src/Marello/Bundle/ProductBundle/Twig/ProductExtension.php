<?php

namespace Marello\Bundle\ProductBundle\Twig;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Provider\ChannelProvider;
use Marello\Bundle\SupplierBundle\Provider\SupplierProvider;

class ProductExtension extends \Twig_Extension
{
    const NAME = 'marello_product';
    
    /** @var ChannelProvider */
    protected $channelProvider;

    /** @var SupplierProvider */
    protected $supplierProvider;

    /**
     * ProductExtension constructor.
     *
     * @param ChannelProvider $channelProvider
     * @param SupplierProvider $supplierProvider
     */
    public function __construct(ChannelProvider $channelProvider, SupplierProvider $supplierProvider)
    {
        $this->channelProvider = $channelProvider;
        $this->supplierProvider = $supplierProvider;
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
                'marello_supplier_get_supplier_ids',
                [$this, 'getSuppliersIds']
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
        return $this->channelProvider->getSalesChannelsIds($product);
    }
    
    /**
     * @param Product $product
     *
     * @return array
     */
    public function getSuppliersIds(Product $product)
    {
        return $this->supplierProvider->getProductSuppliersIds($product);
    }
}
