<?php

namespace Marello\Bundle\InventoryBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseChannelGroupLink;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\InventoryBundle\Model\InventoryBalancer\InventoryBalancer;
use Marello\Bundle\InventoryBundle\Model\VirtualInventory\VirtualInventoryHandler;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesChannelGroupData;

class LoadInventoryData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var Organization
     */
    protected $defaultOrganization;

    /**
     * @var Warehouse
     */
    protected $defaultWarehouse;

    /**
     * @var array
     */
    protected $replenishments;

    /**
     * @var ObjectManager
     */
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

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadProductData::class,
            LoadWarehouseChannelLinkData::class
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

        $this->defaultWarehouse = $this->container
            ->get('marello_inventory.repository.warehouse')
            ->getDefault();

        $replenishmentClass = ExtendHelper::buildEnumValueClassName('marello_inv_reple');
        $this->replenishments = $this->manager->getRepository($replenishmentClass)->findAll();

        $this->loadProductInventory();
    }

    /**
     * load products
     */
    public function loadProductInventory()
    {
        $handle = fopen($this->getDictionary('product_inventory.csv'), "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));

                $this->createProductInventory($data);
            }
            fclose($handle);
        }
        $this->manager->flush();
    }

    /**
     * create new products and inventory items
     * @param array $data
     */
    private function createProductInventory(array $data)
    {
        $product = $this->manager
            ->getRepository('MarelloProductBundle:Product')
            ->findOneBy(['sku' => $data['sku']]);

        if ($product) {
            $inventoryItemManager = $this->container->get('marello_inventory.manager.inventory_item_manager');
            /** @var InventoryItem $inventoryItem */
            $inventoryItem = $inventoryItemManager->getInventoryItem($product);

            if (!$inventoryItem) {
                return;
            }

            $inventoryItem->setReplenishment($this->replenishments[rand(0, count($this->replenishments) - 1)]);
            $this->handleInventoryUpdate($product, $inventoryItem, $data['inventory_qty'], 0, null);
            $this->balanceInventory($product, $data['inventory_qty']);
        }
    }

    /**
     * handle the inventory update for items which have been shipped
     * @param ProductInterface $product
     * @param InventoryItem $item
     * @param $inventoryUpdateQty
     * @param $allocatedInventoryQty
     * @param $entity
     */
    protected function handleInventoryUpdate($product, $item, $inventoryUpdateQty, $allocatedInventoryQty, $entity)
    {
        $context = InventoryUpdateContextFactory::createInventoryUpdateContext(
            $product,
            $item,
            $inventoryUpdateQty,
            $allocatedInventoryQty,
            'import',
            $entity
        );

        /** @var InventoryManager $inventoryManager */
        $inventoryManager = $this->container->get('marello_inventory.manager.inventory_manager');
        $inventoryManager->updateInventoryLevel($context);
    }

    /**
     * @param Product $product
     * @param $inventoryQty $int
     */
    public function balanceInventory($product, $inventoryQty)
    {
        /** @var SalesChannel[] $salesChannels */
        $salesChannels = $product->getChannels();
        foreach ($salesChannels as $salesChannel) {
            $salesChannelGroups[$salesChannel->getGroup()->getId()] = $salesChannel->getGroup();
        }
        /** @var VirtualInventoryHandler $handler */
        $handler = $this->container->get('marello_inventory.model.virtualinventory.virtual_inventory_handler');
        $balancedQty = ($inventoryQty / count($salesChannelGroups));
        foreach ($salesChannelGroups as $salesChannelGroup) {
            /** @var VirtualInventoryLevel $level */
            $level = $handler->findExistingVirtualInventory($product, $salesChannelGroup);
            if (!$level) {
                $level = $handler->createVirtualInventory($product, $salesChannelGroup, $balancedQty);
            }
            $handler->saveVirtualInventory($level, true, true);
        }
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
