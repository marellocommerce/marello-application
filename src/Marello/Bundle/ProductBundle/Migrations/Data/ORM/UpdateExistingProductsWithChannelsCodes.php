<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdateExistingProductsWithChannelsCodes extends AbstractFixture implements ContainerAwareInterface
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
            ->findBy(['channelsCodes' => null]);

        if (count($products) === 0) {
            return;
        }
        foreach ($products as $product) {
            foreach ($product->getChannels() as $channel) {
                $product->addChannelCode($channel->getCode());
            }
            $manager->persist($product);
        }

        $manager->flush();
    }
}
