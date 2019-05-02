<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;

class LoadSupplierData extends AbstractFixture implements DependentFixtureInterface
{
    const SUPPLIER_COST_PERCENTAGE = 0.40;
    const DEFAULT_SUPPLIER_COST = 0.00;

    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array $data
     */
    protected $data = [
        [
            'name' => 'Quiksilver',
            'priority' => 1,
            'can_dropship' => true,
            'is_active' => true,
            'currency' => 'USD',
            'po_send_by' => Supplier::SEND_PO_BY_EMAIL,
            'address'=>
                [
                    'street_address' => '70 Bowman St.',
                    'zipcode' => '06074',
                    'city'=> 'South Windsor',
                    'country'=> 'US',
                    'state' => 'CT'
                ],
            'email' => 'supplier1@email.com'
        ],
        [
            'name' => 'BIC Sport North America, Inc.',
            'priority' => 2,
            'can_dropship' => false,
            'is_active' => true,
            'currency' => 'EUR',
            'po_send_by' => Supplier::SEND_PO_MANUALLY,
            'address'=>
                [
                    'street_address' => '71 Pilgrim Avenue',
                    'zipcode' => '60185',
                    'city'=> 'West Chicago',
                    'country'=> 'US',
                    'state' => 'IL'
                ],
            'email' => 'supplier2@bicsport.com'
        ]
    ];

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadProductData::class,
            LoadProductPriceData::class
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadSuppliers();
        $this->addProductsToSuppliers();
    }

    /**
     * load and create Suppliers
     */
    protected function loadSuppliers()
    {
        $i = 0;

        foreach ($this->data as $values) {
            $supplier = new Supplier();
            $supplier->setName($values['name']);
            $supplier->setPriority($values['priority']);
            $supplier->setCanDropship($values['can_dropship']);
            $supplier->setIsActive($values['is_active']);
            $supplier->setEmail($values['email']);
            $supplier->setCurrency($values['currency']);
            $supplier->setPoSendBy($values['po_send_by']);

            $address = new MarelloAddress();
            $address->setStreet($values['address']['street_address']);
            $address->setPostalCode($values['address']['zipcode']);
            $address->setCity($values['address']['city']);
            $address->setCountry(
                $this->manager
                    ->getRepository('OroAddressBundle:Country')->find($values['address']['country'])
            );
            $address->setRegion(
                $this->manager
                    ->getRepository('OroAddressBundle:Region')
                    ->findOneBy(['combinedCode' => $values['address']['country'] . '-' . $values['address']['state']])
            );
            $this->manager->persist($address);
            $organization = $this->manager
                ->getRepository(Organization::class)
                ->getFirst();
            $supplier->setOrganization($organization);
            $supplier->setAddress($address);
            $this->manager->persist($supplier);
            $this->setReference('marello_supplier_' . $i, $supplier);
            $i++;
        }

        $this->manager->flush();
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
        $percentage = LoadSupplierData::SUPPLIER_COST_PERCENTAGE;
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
