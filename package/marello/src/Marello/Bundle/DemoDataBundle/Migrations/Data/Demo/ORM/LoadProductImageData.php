<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Marello\Bundle\ProductBundle\Entity\Product;

class LoadProductImageData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /** @var User $adminUser  */
    protected $adminUser;

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
        $this->adminUser = $this->manager
            ->getRepository(User::class)->findOneBy(['username' => 'admin']);

        $this->loadProductImages();
    }

    /**
     * load products
     */
    public function loadProductImages()
    {
        $products = $this->manager
            ->getRepository('MarelloProductBundle:Product')
            ->findAll();

        /** @var Product $product */
        foreach ($products as $product) {
            $image = $this->getProductImage($product->getSku());
            if ($image) {
                $product->setImage($image);
                $this->manager->persist($product);
            }
        }

        $this->manager->flush();
    }

    /**
     * @param               $sku
     * @return null
     */
    protected function getProductImage($sku)
    {
        try {
            $imagePath   = $this->getImagePath($sku);
            $fileManager = $this->container->get('oro_attachment.file_manager');
            $image       = $fileManager->createFileEntity($imagePath);
            if ($image) {
                $this->manager->persist($image);
                $image->setOwner($this->adminUser);
            }
        } catch (\Exception $e) {
            //image not found
        }

        return $image;
    }

    /**
     * Get Image path by product sku
     * @param $sku
     * @return string
     */
    protected function getImagePath($sku)
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR);

        $imageNameGuess = sprintf('%s.jpg', $sku);

        foreach ($finder as $file) {
            $pathName = $file->getRelativePathname();
            if ($pathName === $imageNameGuess) {
                return $file->getRealPath();
            } else {
                $skuParts = explode('-', $sku);
                // first two items in the array combined are the number of the SKU and the type of sku
                $skuMain = strtolower(array_shift($skuParts));
                $skuType = array_shift($skuParts);
                if (strpos($pathName, $skuMain) !== false
                && strpos($pathName, $skuType) !== false) {
                    return $file->getRealPath();
                }
            }
        }
    }
}
