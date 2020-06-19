<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Marello\Bundle\ProductBundle\Entity\Variant;
use Marello\Bundle\ProductBundle\Entity\Product;

class LoadProductVariantData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /** @var ObjectManager $manager */
    protected $manager;

    /** @var ContainerInterface $container */
    protected $container;

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadProductData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadProductVariant();
    }

    /**
     * {@inheritdoc}
     */
    public function loadProductVariant()
    {
        $products = $this->manager
            ->getRepository('MarelloProductBundle:Product')
            ->findAll();

        $first = reset($products);

        $parentSkuPattern = null;
        /** @var Product $product */
        foreach ($products as $product) {
            $productSku = $product->getSku();
            $skuPattern = $this->getSkuPattern($productSku);
            if (!$parentSkuPattern) {
                $parentSkuPattern = $skuPattern;
            }

            if ($parentSkuPattern !== $skuPattern || $first->getSku() === $productSku) {
                $result = $this->createVariant($productSku, $skuPattern);
                if ($result) {
                    $parentSkuPattern = null;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     * @param string $sku
     * @param string $skuPattern
     * @return bool
     */
    protected function createVariant($sku, $skuPattern)
    {
        if (!$skuPattern) {
            return false;
        }

        try {
            $variant   = new Variant();
            $variantCode = $this->createVariantCode($sku);
            $variant->setVariantCode($variantCode);
            $skuSearch  = sprintf('%s%%', $skuPattern);
            $products = $this->manager
                ->getRepository('MarelloProductBundle:Product')
                ->createQueryBuilder('p')
                ->where('p.sku LIKE :sku')
                ->setParameter('sku', $skuSearch)
                ->getQuery()
                ->getResult();

            foreach ($products as $product) {
                $variant->addProduct($product);
            }

            $this->manager->persist($variant);
            $this->manager->flush();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * create variant code for product
     * @param $sku
     * @return string
     */
    protected function createVariantCode($sku)
    {
        $hash = hash('md5', $sku);

        return substr($hash, 0, 10);
    }

    /**
     * Get SKU pattern to identify them as a variant
     * @param $sku
     * @return string
     */
    protected function getSkuPattern($sku)
    {
        if (strpos($sku, '-') !== false) {
            return substr($sku, 0, -3);
        }

        return null;
    }
}
