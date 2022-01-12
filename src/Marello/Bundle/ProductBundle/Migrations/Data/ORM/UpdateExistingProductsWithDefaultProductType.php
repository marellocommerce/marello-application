<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdateExistingProductsWithDefaultProductType extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $products = $manager
            ->getRepository(Product::class)
            ->findBy(['type' => null]);

        if (count($products) === 0) {
            return;
        }
        $productTypesProvider = $this->container->get('marello_product.provider.product_types');
        $defaultProductType = $productTypesProvider->getProductType(Product::DEFAULT_PRODUCT_TYPE);

        foreach ($products as $product) {
            $product->setType($defaultProductType->getName());
            $manager->persist($product);
        }

        $manager->flush();
    }
}
