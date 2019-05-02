<?php

namespace Marello\Bundle\SupplierBundle\Twig;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SupplierBundle\Provider\SupplierProvider;

class SupplierExtension extends \Twig_Extension
{
    const NAME = 'marello_supplier';

    /** @var SupplierProvider */
    protected $supplierProvider;

    /**
     * SupplierExtension constructor.
     *
     * @param SupplierProvider $supplierProvider
     */
    public function __construct(SupplierProvider $supplierProvider)
    {
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
    public function getSuppliersIds(Product $product)
    {
        return $this->supplierProvider->getProductSuppliersIds($product);
    }
}
