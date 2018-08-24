<?php
namespace Marello\Bundle\MagentoBundle\ImportExport\Converter;

use Marello\Bundle\MagentoBundle\Entity\Store;

class PriceDataConverter extends ProductDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $price = $exportedRecord["product"]["general_prices"]["default"]["value"];
        $specialPrice = $exportedRecord["product"]["general_prices"]["special"]["value"];
        $msrpPrice = $exportedRecord["product"]["general_prices"]["msrp"]["value"];

        $store = false;
        if (isset($exportedRecord['product']['channel_prices'])) {
            $price = $exportedRecord["product"]["channel_prices"]["default"]["value"];
            $specialPrice = $exportedRecord["product"]["channel_prices"]["special"]["value"];

            /**
             * multiple websites would not set price on website level
             */
            $originIds = $this->getWebsiteIds();
            if (count($originIds) <= 1) {
                $mageWebsiteId = $this->getIntegrationChannel()->getTransport()->getWebsiteId();
                $storeObj = $this->getMagentoStore($mageWebsiteId);
                if ($storeObj) {
                    $store = $storeObj->getCode();
                }
            }
        }
        $result = [
            'productId'         => $exportedRecord["product"]['product_id'],
            'productData'       => [
                'price'         => $price,
                'special_price' => $specialPrice,
                'msrp'          => $msrpPrice
            ]
        ];

        if ($store) {
            $result['store'] = $store;
        }

        return $result;
    }

    /**
     * @param $websiteId
     * @return Store
     */
    protected function getMagentoStore($websiteId)
    {
        return $this->getEntityManager()
            ->getRepository(Store::class)
            ->findOneBy(['originId' => $websiteId]);
    }
}
