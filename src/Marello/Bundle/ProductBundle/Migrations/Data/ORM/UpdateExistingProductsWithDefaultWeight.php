<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Marello\Bundle\ProductBundle\Entity\Product;

class UpdateExistingProductsWithDefaultWeight extends AbstractFixture implements ContainerAwareInterface
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
            ->findBy(['weight' => null]);

        if (count($products) === 0) {
            return;
        }

        /** @var Product $product */
        foreach ($products as $product) {
            $product->setWeight(0);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
