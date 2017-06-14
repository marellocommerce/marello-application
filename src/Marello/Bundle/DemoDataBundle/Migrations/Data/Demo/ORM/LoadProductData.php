<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Marello\Bundle\ProductBundle\Entity\ProductChannelTaxRelation;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\SupplierBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class LoadProductData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /** @var \Oro\Bundle\OrganizationBundle\Entity\Organization $defaultOrganization  */
    protected $defaultOrganization;

    /** @var \Marello\Bundle\InventoryBundle\Entity\Warehouse $defaultWarehouse */
    protected $defaultWarehouse;

    /** @var ObjectManager $manager */
    protected $manager;

    protected $replenishments;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getDependencies()
    {
        return [
            LoadSalesData::class,
            LoadTaxCodeData::class
        ];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $organizations = $this->manager
            ->getRepository('OroOrganizationBundle:Organization')
            ->findAll();

        if (is_array($organizations) && count($organizations) > 0) {
            $this->defaultOrganization = array_shift($organizations);
        }

        $this->defaultWarehouse = $this->manager
            ->getRepository('MarelloInventoryBundle:Warehouse')
            ->getDefault();

        $replenishmentClass = ExtendHelper::buildEnumValueClassName('marello_product_reple');
        $this->replenishments = $this->manager->getRepository($replenishmentClass)->findAll();

        $this->loadProducts();
    }

    /**
     * load products
     */
    public function loadProducts()
    {
        $handle = fopen($this->getDictionary('products.csv'), "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));

                $product = $this->createProduct($data);
                $this->setReference('marello-product-' . $i, $product);
                $i++;
            }
            fclose($handle);
        }
        $this->manager->flush();
    }

    /**
     * create new products and inventory items
     * @param array $data
     * @return Product $product
     */
    private function createProduct(array $data)
    {
        $product = new Product();
        $product->setSku($data['sku']);
        $product->setName($data['name']);
        $product->setDesiredStockLevel(rand($data['stock_level'], $data['stock_level'] + 10));
        $product->setPurchaseStockLevel(rand(1, $product->getDesiredStockLevel()));
        $product->setOrganization($this->defaultOrganization);
        $product->setWeight(mt_rand(50, 300) / 100);
        $inventoryItem = new InventoryItem($this->defaultWarehouse, $product);
        $this->handleInventoryUpdate($inventoryItem, $data['stock_level'], 0, null);
        $product->getInventoryItems()->add($inventoryItem);

        $status = $this->manager
            ->getRepository('MarelloProductBundle:ProductStatus')
            ->findOneByName($data['status']);

        $product->setStatus($status);
        $channels = explode(';', $data['channel']);
        $currencies = [];
        foreach ($channels as $channelId) {
            $channel = $this->getReference('marello_sales_channel_'. (int)$channelId);
            $product->addChannel($channel);
            $currencies[] = $channel->getCurrency();
        }

        $currencies = array_unique($currencies);
        /**
         * add default prices for all currencies
         */
        foreach ($currencies as $currency) {
            // add prices
            $price = new ProductPrice();
            $price->setCurrency($currency);
            if (count($currencies) > 1 && $currency === 'USD') {
                $price->setValue(($data['price'] * 1.12));
            } else {
                $price->setValue($data['price']);
            }

            $product->addPrice($price);
        }

        /**
        * set default taxCode and taxcodes per saleschannel
        */
        $product->setTaxCode($this->getReference('marello_taxcode_'. rand(0, 2)));
        foreach ($channels as $channelId) {
            $channel = $this->getReference('marello_sales_channel_'. (int)$channelId);

            $productSalesChannelRelation = new ProductChannelTaxRelation();
            $productSalesChannelRelation
                ->setProduct($product)
                ->setSalesChannel($channel)
                ->setTaxCode($this->getReference('marello_taxcode_'. rand(0, 2)))
            ;
            $this->manager->persist($productSalesChannelRelation);
            $product->addSalesChannelTaxCode($productSalesChannelRelation);
        }

        /**
         * add suppliers per product
         */
        $this->addProductSuppliers($product);

        $product->setReplenishment($this->replenishments[rand(0, count($this->replenishments) - 1)]);

        $this->manager->persist($product);

        return $product;
    }

    /**
     * Add product suppliers to product
     * @param Product $product
     */
    protected function addProductSuppliers(Product $product)
    {
        $suppliers = $this->manager
            ->getRepository('MarelloSupplierBundle:Supplier')
            ->findAll();

        foreach ($suppliers as $supplier) {
            $productSupplierRelation1 = new ProductSupplierRelation();
            $productSupplierRelation1
                ->setProduct($product)
                ->setSupplier($supplier)
                ->setQuantityOfUnit(100)
                ->setCanDropship(true)
                ->setPriority(rand(1, 6))
                ->setCost($this->getRandomFloat(45, 60))
            ;
            $this->manager->persist($productSupplierRelation1);
            $product->addSupplier($productSupplierRelation1);

            $productSupplierRelation2 = new ProductSupplierRelation();
            $productSupplierRelation2
                ->setProduct($product)
                ->setSupplier($supplier)
                ->setQuantityOfUnit(400)
                ->setCanDropship(true)
                ->setPriority(rand(1, 6))
                ->setCost($this->getRandomFloat(20, 40))
            ;
            $this->manager->persist($productSupplierRelation2);
            $product->addSupplier($productSupplierRelation2);

            $productSupplierRelation3 = new ProductSupplierRelation();
            $productSupplierRelation3
                ->setProduct($product)
                ->setSupplier($supplier)
                ->setQuantityOfUnit(750)
                ->setCanDropship(true)
                ->setPriority(rand(1, 6))
                ->setCost($this->getRandomFloat(10, 15))
            ;
            $this->manager->persist($productSupplierRelation3);
            $product->addSupplier($productSupplierRelation3);
        }

        $preferredSupplier = null;
        $preferredPriority = 0;
        foreach ($product->getSuppliers() as $productSupplierRelation) {
            if (null == $preferredSupplier) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
                continue;
            }
            if ($productSupplierRelation->getPriority() < $preferredPriority) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
            }
        }

        if ($preferredSupplier) {
            $product->setPreferredSupplier($preferredSupplier);
        }
    }

    /**
     * Get random float
     * @param $min
     * @param $max
     * @return mixed
     */
    private function getRandomFloat($min, $max)
    {
        return ($min + lcg_value()*(abs($max - $min)));
    }

    /**
     * handle the inventory update for items which have been shipped
     * @param InventoryItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param $entity
     */
    protected function handleInventoryUpdate($item, $inventoryUpdateQty, $allocatedInventoryQty, $entity)
    {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $item,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            'import',
            $entity
        );

        /** @var InventoryManager $inventoryManager */
        $inventoryManager = $this->container->get('marello_inventory.manager.inventory_manager');
        $inventoryManager->updateInventoryItems($context);
    }

    /**
     * Get dictionary file by name
     * @param $name
     * @return string
     */
    protected function getDictionary($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
    }
}
