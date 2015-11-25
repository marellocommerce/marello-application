<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;

class LoadProductData extends AbstractFixture implements DependentFixtureInterface
{
    protected $defaultOrganization;
    protected $defaultWarehouse;

    public function getDependencies() {
        return [
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductStatusData',
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSalesData'
        ];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->defaultOrganization = $manager->getRepository('OroOrganizationBundle:Organization')->getOrganizationById(1);
        $this->defaultWarehouse = $manager->getRepository('MarelloInventoryBundle:Warehouse')->getDefault();

        $this->loadProducts($manager);
        $manager->flush();
    }

    /**
     *
     */
    public function loadProducts(ObjectManager $manager)
    {
        $handle = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . "products.csv", "r");
        if ($handle) {
            $headers = array();
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));

                $product = $this->createProduct($data, $manager);
                $this->setReference('marello-product-' . $i, $product);
                $i++;
            }
            fclose($handle);
        }
    }

    private function createProduct(array $data, $manager)
    {
        $product = new Product();
        $product->setSku($data['sku']);
        $product->setPrice($data['price']);
        $product->setName($data['name']);
        $inventoryItem = new InventoryItem();
        $inventoryItem->setProduct($product);
        $inventoryItem->setWarehouse($this->defaultWarehouse);
        $inventoryItem->setQuantity($data['stock_level']);
        $product->getInventoryItems()->add($inventoryItem);

        $randomNumber = rand(0,100);
        $status = ($randomNumber % 2 == 0) ? $this->getReference('product_status_enabled') : $this->getReference('product_status_disabled');
        $product->setStatus($status);
        $product->addChannel($this->getReference('marello_sales_channel_0'));
        $product->addChannel($this->getReference('marello_sales_channel_1'));

        $manager->persist($product);

        return $product;
    }
}
