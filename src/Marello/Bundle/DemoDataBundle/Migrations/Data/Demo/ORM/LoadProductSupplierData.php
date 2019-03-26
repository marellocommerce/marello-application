<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;

class LoadProductSupplierData extends AbstractFixture implements DependentFixtureInterface
{
    const SUPPLIER_COST_PERCENTAGE = 0.40;
    const DEFAULT_SUPPLIER_COST = 0.00;

    /** @var ObjectManager $manager */
    protected $manager;

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
        /** @var Product $product */
        $product = $this->manager->getRepository(Product::class)->findOneBySku($data['sku']);
        if (!$product) {
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
     * Get dictionary file by name
     * @param $name
     * @return string
     */
    protected function getDictionary($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
    }
}
