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
        $store = false;
        if (isset($exportedRecord['product']['channel_prices'])) {
            $price = (float)$exportedRecord['product']['channel_prices']['0']['value'];
            $store = 'sales_channel_de_webshop';
        }
        $result = [
            'productId'     => $exportedRecord["product"]['product_id'],
            'productData'   => [
                'price'     => $price
            ]
        ];

        if ($store) {
            $result['store'] = $store;
        }

        return $result;
    }
}
