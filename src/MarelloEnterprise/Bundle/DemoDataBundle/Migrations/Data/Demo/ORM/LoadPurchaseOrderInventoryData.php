<?php

namespace MarelloEnterprise\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Model\InventoryTotalCalculator;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData;
use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductInventoryData as BaseProductInventoryData;

class LoadPurchaseOrderInventoryData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    const INVENTORY_ITEM_TOTAL = 60;
    const REPLENISHMENT_STATUS = 'never_out_of_stock';

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
            BaseProductInventoryData::class,
            LoadWarehouseData::class
        ];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadProductInventory();
    }

    /**
     * load products
     */
    public function loadProductInventory()
    {
        $inventoryItems = $this->manager
            ->getRepository(InventoryItem::class)
            ->findAll();

        /** @var InventoryTotalCalculator $inventoryTotalCalculator */
        $inventoryTotalCalculator = $this->container->get('marello_inventory.model.inventory_level_totals_calculator');
        $replenishmentClass = ExtendHelper::buildEnumValueClassName('marello_inv_reple');
        $replenishment = $this->manager->getRepository($replenishmentClass)->find(self::REPLENISHMENT_STATUS);
        /** @var InventoryItem $inventoryItem */
        foreach ($inventoryItems as $inventoryItem) {
            $total = $inventoryTotalCalculator->getTotalInventoryQty($inventoryItem);
            if ($total <= self::INVENTORY_ITEM_TOTAL) {
                $inventoryItem->setPurchaseInventory($total * 1.5);
                $inventoryItem->setDesiredInventory($total * 1.75);
                $inventoryItem->setReplenishment($replenishment);
                $this->manager->persist($inventoryItem);
            }
        }
        $this->manager->flush();
    }
}
