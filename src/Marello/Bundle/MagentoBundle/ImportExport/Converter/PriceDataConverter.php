<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 18/05/2018
 * Time: 09:22
 */

namespace Marello\Bundle\MagentoBundle\ImportExport\Converter;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ImportExportBundle\Converter\DefaultDataConverter;

use Marello\Bundle\MagentoBundle\Entity\Product;
use Marello\Bundle\MagentoBundle\Provider\EntityManagerTrait;

class PriceDataConverter extends DefaultDataConverter
{
    use EntityManagerTrait;

    public function __construct(EntityManager $entityManager)
    {
        $this->setEntityManager($entityManager);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $result = parent::convertToExportFormat($exportedRecord, $skipNullValues);

        return [
            'productId' => $this->getProductOrigin($result['product:sku']),
            'productData' => [
                'price' => (float)$result['value']
            ]
        ];
    }

    /**
     * @param $sku
     * @return int
     * @throws \Exception
     */
    protected function getProductOrigin($sku)
    {
        $search = ['sku' => $sku];

        /**
         * @var $product Product
         */
        $product = $this->getEntityManager()->getRepository('MarelloMagentoBundle:Product')->findOneBy($search);

        if ($product) {
            return $product->getOriginId();
        }

        throw new \Exception("product must already be in magento!");
    }
}
