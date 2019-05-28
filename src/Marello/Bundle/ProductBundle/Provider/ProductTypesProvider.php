<?php

namespace Marello\Bundle\ProductBundle\Provider;

use Marello\Bundle\ProductBundle\Model\ProductTypeInterface;

class ProductTypesProvider
{
    /**
     * @var ProductTypeInterface[]
     */
    private $types = [];

    /**
     * @param ProductTypeInterface $productType
     * @return $this
     */
    public function addProductType(ProductTypeInterface $productType)
    {
        $name = $productType->getName();
        if ($this->hasProductType($name)) {
            throw new \LogicException(sprintf('Product Type with name "%s" already registered', $name));
        }
        $this->types[$name] = $productType;

        return $this;
    }

    /**
     * @return ProductTypeInterface[]
     */
    public function getProductTypes()
    {
        return $this->types;
    }

    /**
     * @param string $name
     * @return ProductTypeInterface|null
     */
    public function getProductType($name)
    {
        if ($this->hasProductType($name)) {
            return $this->types[$name];
        }

        return null;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasProductType($name)
    {
        return isset($this->types[$name]);
    }
}
