<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Converter;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

use Marello\Bundle\MagentoBundle\Entity\Category;
use Marello\Bundle\MagentoBundle\Provider\EntityManagerTrait;

class CategoryDataConverter extends IntegrationAwareDataConverter
{
    use EntityManagerTrait;

    public function __construct(EntityManager $entityManager)
    {
        $this->setEntityManager($entityManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'category_id'       => 'originId',
            'name'              => 'name',
            'code'              => 'code',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $result = parent::convertToImportFormat($importedRecord, $skipNullValues);

        $dateObj = new \DateTime('now', new \DateTimeZone('UTC'));
        $date = $dateObj->format('Y-m-d H:i:s');
        $result['createdAt'] = $date;
        $result['updatedAt'] = $date;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $mageCategoryId = $this->getMagentoCategoryId($exportedRecord['code']);
        $mageProductIds = $this->getLinkedProducts($exportedRecord);

        $result = ['categoryId' => $mageCategoryId, 'products' => $mageProductIds];
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }

    /**
     * @param $code
     * @return int
     * @throws \Exception
     */
    protected function getMagentoCategoryId($code)
    {
        $search = ['code' => $code];

        /**
         * @var $category Category
         */
        $category = $this->getEntityManager()
                        ->getRepository('MarelloMagentoBundle:Category')
                        ->findOneBy($search);

        if ($category) {
            return $category->getOriginId();
        }

        throw new \Exception("category must already be in magento!");
    }


    /**
     * @param $sku
     * @return int
     * @throws \Exception
     */
    protected function getLinkedProducts(array $record)
    {
        $linkedSkus = $this->prepareSkus($record);

        $qb = $this->getEntityManager()
                ->getRepository('MarelloMagentoBundle:Product')
                ->createQueryBuilder('o');
        $qb
            ->andWhere('o.sku' . ' IN (:skus)')
            ->setParameter('skus', $linkedSkus);

        $mageProductIds = [];
        foreach ($qb->getQuery()->getResult() as $mageProduct) {
            $mageProductIds[] = $mageProduct->getOriginId();
        }
        return $mageProductIds;
    }

    /**
     * @param array $record
     * @return array
     */
    protected function prepareSkus(array $record)
    {
        $skus = [];
        foreach ($record['products'] as $_sku) {
            $skus[] = $_sku['sku'];
        }
        return $skus;
    }
}
