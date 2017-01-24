<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\InventoryBundle\Event\InventoryUpdateEvent;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class LoadProductData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /** @var \Oro\Bundle\OrganizationBundle\Entity\Organization $defaultOrganization  */
    protected $defaultOrganization;

    /** @var \Marello\Bundle\InventoryBundle\Entity\Warehouse $defaultWarehouse */
    protected $defaultWarehouse;

    /** @var ObjectManager $manager */
    protected $manager;

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

        $this->manager->persist($product);

        return $product;
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
        $inventoryItems[] = $item;
        $inventoryItemData = [];
        foreach ($inventoryItems as $inventoryItem) {
            $inventoryItemData[] = [
                'item'          => $inventoryItem,
                'qty'           => $inventoryUpdateQty,
                'allocatedQty'  => $allocatedInventoryQty
            ];
        }

        $data = [
            'stock'             => $inventoryUpdateQty,
            'allocatedStock'    => $allocatedInventoryQty,
            'trigger'           => 'import',
            'items'             => $inventoryItemData,
            'relatedEntity'     => $entity
        ];

        $context = InventoryUpdateContext::createUpdateContext($data);
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
