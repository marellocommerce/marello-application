<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\ProductBundle\Entity\Builder\ProductFamilyBuilder;
use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadProductData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var Organization
     */
    protected $defaultOrganization;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     * @return array
     */
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
                $this->setReference('marello_product_' . $product->getSku(), $product);
                $i++;
            }
            fclose($handle);
        }
        $this->manager->flush();
    }

    /**
     * create new products
     * @param array $data
     * @return Product $product
     */
    private function createProduct(array $data)
    {
        $name = new LocalizedFallbackValue();
        $name->setString($data['name']);
        
        $product = new Product();
        $product->setSku($data['sku']);
        $product->addName($name);
        $product->setOrganization($this->defaultOrganization);
        $product->setWeight($data['weight']);
        $product->setManufacturingCode($this->generateManufacturingCode($data['sku']));

        $status = $this->manager
            ->getRepository('MarelloProductBundle:ProductStatus')
            ->findOneByName($data['status']);

        $product->setStatus($status);
        $channels = explode(';', $data['channel']);
        foreach ($channels as $channelCode) {
            /** @var SalesChannel $channel */
            $channel = $this->getReference($channelCode);
            $product->addChannel($channel);
        }

        $productTypesProvider = $this->container->get('marello_product.provider.product_types');
        $defaultProductType = $productTypesProvider->getProductType(Product::DEFAULT_PRODUCT_TYPE);
        if ($defaultProductType) {
            $product->setType($defaultProductType->getName());
        }

        /**
        * set default taxCode
        */
        $product->setTaxCode($this->getReference(LoadTaxCodeData::TAXCODE_1_REF));
        $this->manager->persist($product);

        /** @var AttributeFamily $attributeFamily */
        $attributeFamily = $this->manager
            ->getRepository(AttributeFamily::class)
            ->findOneByCode(ProductFamilyBuilder::DEFAULT_FAMILY_CODE);
        $product->setAttributeFamily($attributeFamily);

        return $product;
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

    /**
     * @param string $sku
     * @return string
     */
    private function generateManufacturingCode($sku)
    {
        return sprintf(
            '%s-%s-%s-%s',
            substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 3),
            substr(str_shuffle("0123456789"), 0, 3),
            substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 3),
            substr(str_shuffle(strtolower(str_replace('-', '', $sku))), 0, 3)
        );
    }
}
