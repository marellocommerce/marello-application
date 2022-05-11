<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;
use Marello\Bundle\InventoryBundle\Entity\InventoryItem;
use Marello\Bundle\InventoryBundle\Entity\InventoryLevel;
use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\InventoryBundle\Entity\WarehouseType;
use Marello\Bundle\InventoryBundle\Provider\WarehouseTypeProviderInterface;
use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadProductSupplierData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    const SUPPLIER_COST_PERCENTAGE = 0.40;
    const DEFAULT_SUPPLIER_COST = 0.00;

    /** @var ObjectManager $manager */
    protected $manager;

    /** @var Warehouse $warehouse */
    protected $warehouse;

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadSupplierData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->addProductsToSuppliers();
        $this->updateCurrentSuppliers();
    }

    /**
     * load products
     */
    public function addProductsToSuppliers()
    {
        $handle = fopen($this->getDictionary('product_suppliers.csv'), "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));
                $this->addProductSuppliers($data);
            }
            fclose($handle);
        }
        $this->manager->flush();
    }

    /**
     * Add product suppliers to product
     * @param array $data
     */
    protected function addProductSuppliers(array $data)
    {
        /** @var AclHelper $aclHelper */
        $aclHelper = $this->container->get('oro_security.acl_helper');
        /** @var Product $product */
        $product = $this->manager->getRepository(Product::class)->findOneBySku($data['sku'], $aclHelper);
        if (!$product) {
            return;
        }

        $existingSupplierRelation = $this->manager
            ->getRepository(ProductSupplierRelation::class)
            ->findOneBy(['product' => $product]);

        if ($existingSupplierRelation) {
            return;
        }

        $suppliers = $this->manager
            ->getRepository('MarelloSupplierBundle:Supplier')
            ->findBy([
                'name' => $data['supplier']
            ]);

        foreach ($suppliers as $supplier) {
            $productSupplierRelation = new ProductSupplierRelation();
            $productSupplierRelation
                ->setProduct($product)
                ->setSupplier($supplier)
                ->setQuantityOfUnit(1)
                ->setCanDropship(true)
                ->setPriority(1)
                ->setCost($this->calculateSupplierCost($product))
            ;
            $this->manager->persist($productSupplierRelation);
            $product->addSupplier($productSupplierRelation);
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
     * Calculate the cost for the supplier based of a static percentage
     * of the retail price
     * @param Product $product
     * @return float $supplierCost
     */
    private function calculateSupplierCost(Product $product)
    {
        $percentage = self::SUPPLIER_COST_PERCENTAGE;
        $assembledPriceListReference = sprintf('marello_product_price_%s', $product->getSku());
        if (!$this->hasReference($assembledPriceListReference)) {
            return self::DEFAULT_SUPPLIER_COST;
        }

        $assembledPriceList = $this->getReference($assembledPriceListReference);
        $supplierCost = $assembledPriceList->getDefaultPrice()->getValue() * $percentage;

        return $supplierCost;
    }

    /**
     * Persist current suppliers that support dropshipping
     * in order to create external warehouse
     */
    public function updateCurrentSuppliers()
    {
        $suppliers = $this->manager
            ->getRepository('MarelloSupplierBundle:Supplier')
            ->findBy(['canDropship' => true]);

        /** @var Supplier $supplier */
        foreach ($suppliers as $supplier) {
            $warehouse = $this->getWarehouse($supplier);
            if (!$warehouse) {
                $warehouse = $this->createWarehouse($supplier);
            }
            $this->createInventoryLevelsForRelatedProducts($supplier, $warehouse);
        }
        $this->manager->flush();
    }

    /**
     * @param Supplier $supplier
     * @param Warehouse $warehouse
     */
    private function createInventoryLevelsForRelatedProducts(Supplier $supplier, Warehouse $warehouse)
    {
        $productSupplierRelations = $this->manager
            ->getRepository(ProductSupplierRelation::class)
            ->findBy(['supplier' => $supplier, 'canDropship' => true]);

        foreach ($productSupplierRelations as $productSupplierRelation) {
            $this->createInventoryLevelForRelatedProduct($productSupplierRelation, $warehouse);
        }

        $this->manager->flush();
    }

    /**
     * @param Supplier $supplier
     * @return Warehouse
     */
    private function getWarehouse(Supplier $supplier)
    {
        if ($this->warehouse === null) {
            $warehouseType = $this->manager
                ->getRepository(WarehouseType::class)
                ->find(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);
            $warehouse = $this->manager
                ->getRepository(Warehouse::class)
                ->findOneBy([
                    'code' => $supplier->getCode(),
                    'warehouseType' => $warehouseType
                ]);
            if ($warehouse) {
                $this->warehouse = $warehouse;
            }
        }

        return $this->warehouse;
    }

    /**
     * @param Supplier $supplier
     * @return Warehouse
     */
    private function createWarehouse(Supplier $supplier)
    {
        $warehouseType = $this->manager
            ->getRepository(WarehouseType::class)
            ->find(WarehouseTypeProviderInterface::WAREHOUSE_TYPE_EXTERNAL);

        $warehouse = new Warehouse(sprintf('%s External Warehouse', $supplier->getName()));
        $warehouse
            ->setAddress(clone $supplier->getAddress())
            ->setCode($supplier->getCode())
            ->setWarehouseType($warehouseType);
        if ($organization = $supplier->getOrganization()) {
            $warehouse->setOwner($organization);
        }

        $this->manager->persist($warehouse);
        $this->manager->flush();

        return $warehouse;
    }

    /**
     * @param ProductSupplierRelation $productSupplierRelation
     * @param Warehouse $warehouse
     */
    private function createInventoryLevelForRelatedProduct(
        ProductSupplierRelation $productSupplierRelation,
        Warehouse $warehouse
    ) {
        $inventoryItem = $this->manager
            ->getRepository(InventoryItem::class)
            ->findOneByProduct($productSupplierRelation->getProduct());
        $existingInvLevel = $inventoryItem->getInventoryLevel($warehouse);
        if (!$existingInvLevel) {
            $inventoryLevel = new InventoryLevel();
            $inventoryLevel
                ->setInventoryItem($inventoryItem)
                ->setWarehouse($warehouse)
                ->setInventoryQty(0)
                ->setOrganization($inventoryItem->getOrganization())
                ->setManagedInventory(false);
            $this->manager->persist($inventoryLevel);
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
