<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\CatalogBundle\Entity\Category;

class LoadCategoryData extends AbstractFixture implements DependentFixtureInterface
{
    const SKU_PATTERN = '10';
    /**
     * @var ObjectManager
     */
    protected $manager;

    public function getDependencies()
    {
        return [
            LoadProductData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $organization = $manager
            ->getRepository(Organization::class)
            ->getFirst();

        $handle = fopen($this->getDictionary('categories.csv'), "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 2000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 2000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));
                /** @var Category $category */
                $category = $this->createCategory($data, $organization);
                $this->setReference('marello-category-' . $i, $category);
                $i++;
            }
            $this->closeFiles($handle);
        }
        $this->manager->flush();
    }

    /**
     * Close all open files.
     */
    protected function closeFiles($handle)
    {
        if ($handle) {
            fclose($handle);
        }
    }

    /**
     * @param array        $row
     * @param Organization $organization
     *
     * @return Category
     */
    protected function createCategory($row, Organization $organization)
    {
        $category = new Category();

        $category->setName($row['name']);
        $category->setCode($row['code']);

        if (!empty($row['excluded_products'])) {
            $excludedProductsSkus = str_replace(';', ',', $row['excluded_products']);
            $qb = $this->getQueryBuilder();
            $products = $qb
                ->where('p.sku NOT IN (:sku)')
                ->setParameter('sku', $excludedProductsSkus)
                ->getQuery()
                ->getResult();
        } else {
            $skuSearch  = sprintf('%s%%', self::SKU_PATTERN);
            $qb = $this->getQueryBuilder();
            $products = $qb
                ->where('p.sku LIKE :sku')
                ->setParameter('sku', $skuSearch)
                ->getQuery()
                ->getResult();
        }

        foreach ($products as $product) {
            $category->addProduct($product);
        }

        $category->setOrganization($organization);
        $this->manager->persist($category);
        
        return $category;
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
     * {@inheritdoc}
     * @return QueryBuilder
     */
    private function getQueryBuilder()
    {
        return $this->manager
            ->getRepository('MarelloProductBundle:Product')
            ->createQueryBuilder('p');
    }
}
