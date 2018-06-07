<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

class PriceNormalizer extends ConfigurableEntityNormalizer
{
    use TraitEntityNormalizer;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $result = parent::normalize($object, $format, $context);
        foreach ($object->getProduct()->getChannelPrices() as $price) {
            if (!$price->getChannel()->getIntegrationChannel()) {
                continue;
            }
            if ($price->getChannel()->getIntegrationChannel()->getId() == $context['channel']) {
                $result['product']['channel_prices'][] = [
                    'value'         => $price->getValue(),
                    'currency'      => $price->getCurrency(),
                    'channel_id'    => $price->getChannel()->getId(),
                ];
            }
        }
        $result['product']['product_id'] = $object->getProduct()->getId();
        $result['integration_channel_id'] = $context['channel'];
        return $result;
    }
}
