<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Serializer;

use Marello\Bundle\PricingBundle\Entity\AssembledPriceList;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

use Marello\Bundle\PricingBundle\Entity\AssembledChannelPriceList;

class PriceNormalizer extends ConfigurableEntityNormalizer
{
    use TraitEntityNormalizer;

    const SPECIAL_PRICE = 'special';
    const DEFAULT_PRICE = 'default';
    const MSRP_PRICE = 'msrp';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $result = parent::normalize($object, $format, $context);

        /** @var AssembledPriceList $defaultPrice */
        $defaultPrice = $object->getProduct()->getPrice();

        foreach ([self::DEFAULT_PRICE, self::SPECIAL_PRICE, self::MSRP_PRICE] as $priceType) {
            $result['product']['general_prices'][$priceType] = $this->getPriceData($defaultPrice, $priceType);
        }

        /** @var AssembledChannelPriceList $price */
        foreach ($object->getProduct()->getChannelPrices() as $price) {
            if (!$price->getChannel()->getIntegrationChannel()) {
                continue;
            }
            if ($price->getChannel()->getIntegrationChannel()->getId() == $context['channel']) {
                foreach ([self::DEFAULT_PRICE, self::SPECIAL_PRICE] as $priceType) {
                    $result['product']['channel_prices'][$priceType] = $this->getPriceData($price, $priceType);
                }
            }
        }

        $result['product']['product_id'] = $object->getProduct()->getId();
        $result['product']['sku'] = $object->getProduct()->getSku();
        $result['integration_channel_id'] = $context['channel'];

        return $result;
    }

    /**
     * @param AssembledPriceList | AssembledChannelPriceList $price
     * @param string $priceType
     * @return array|null
     */
    private function getPriceData($price, $priceType = self::DEFAULT_PRICE)
    {
        $value = 0.0000;
        switch ($priceType) {
            case $priceType == self::MSRP_PRICE && $price instanceof AssembledPriceList:
                if ($price->getMsrpPrice()) {
                    $value = $price->getMsrpPrice()->getValue();
                }
                break;
            case $priceType == self::SPECIAL_PRICE:
                if ($price->getSpecialPrice()) {
                    $value = $price->getSpecialPrice()->getValue();
                }
                break;
            default:
                $value = $price->getDefaultPrice()->getValue();
                break;
        }
        $value = (float)$value;

        $result = [
            'value' => $value,
            'currency' => $price->getCurrency(),
        ];

        if ($price instanceof AssembledChannelPriceList) {
            $result['channel_id'] = $price->getChannel()->getId();
        }

        return $value <= 0 ? null : $result;
    }
}
