<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdateExistingProductsWithLocalizedName extends AbstractFixture implements ContainerAwareInterface
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
        /** @var Product[] $products */
        $products = $manager
            ->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.names', 'n')
            ->having('COUNT(n.id) = 0')
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();

        if (count($products) === 0) {
            return;
        }

        foreach ($products as $product) {
            $product->setDefaultName($product->getDenormalizedDefaultName());
            $manager->persist($product);
        }

        $manager->flush();
    }
}
