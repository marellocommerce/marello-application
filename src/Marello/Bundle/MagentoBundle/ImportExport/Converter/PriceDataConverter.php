<?php
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
        $price = (float)$exportedRecord['value'];
        if (isset($exportedRecord['product']['channel_prices'])) {
            $price = (float)$exportedRecord['product']['channel_prices']['0']['value'];
        }
        $result = [
            'productId'     => $exportedRecord["product"]['product_id'],
            'productData'   => [
                'price'     => $price
            ]
        ];

        return $result;
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
