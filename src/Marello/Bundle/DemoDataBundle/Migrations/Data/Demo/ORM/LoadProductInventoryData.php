<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\ProductBundle\Entity\ProductInterface;
use Marello\Bundle\InventoryBundle\Manager\InventoryManager;
use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContextFactory;

class LoadProductInventoryData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
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
        ];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

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

            $desiredInventory = round(($data['inventory_qty'] * 1.75));
            $inventoryItem->setDesiredInventory($desiredInventory);
            $purchaseInventory = round(($data['inventory_qty'] * 1.5));
            $inventoryItem->setPurchaseInventory($purchaseInventory);

            $inventoryItem->setReplenishment($this->replenishments[rand(0, count($this->replenishments) - 1)]);
            $this->handleInventoryUpdate($product, $inventoryItem, $data['inventory_qty'], 0, null);
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
     * Get dictionary file by name
     * @param $name
     * @return string
     */
    protected function getDictionary($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
    }
}
