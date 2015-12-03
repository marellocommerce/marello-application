<?php

namespace Marello\Bundle\OrderBundle\Form\DataTransformer;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProductToSkuTransformer implements DataTransformerInterface
{

    /** @var Registry */
    protected $doctrine;

    /**
     * ProductToSkuTransformer constructor.
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value instanceof Product) {
            return null;
        }

        return $value->getSku();
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        $product = $this->doctrine->getRepository('MarelloProductBundle:Product')->findOneBy(['sku' => $value]);

        if (!$product) {
            throw new TransformationFailedException(sprintf('Product with SKU: "%s" not found.', $value));
        }

        return $product;
    }
}
