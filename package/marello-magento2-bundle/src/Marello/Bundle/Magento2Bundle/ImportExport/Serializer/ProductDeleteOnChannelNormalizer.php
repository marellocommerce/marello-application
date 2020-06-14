<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Serializer;

use Marello\Bundle\Magento2Bundle\DTO\ProductDeleteOnChannelDTO;
use Marello\Bundle\Magento2Bundle\ImportExport\Message\ProductDeleteOnChannelMessage;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;

class ProductDeleteOnChannelNormalizer implements NormalizerInterface
{
    /**
     * @param ProductDeleteOnChannelDTO $object
     * @param string $format
     * @param array $context
     * @return ProductDeleteOnChannelMessage
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return ProductDeleteOnChannelMessage::create(
            $object->getInternalMagentoProduct()->getId(),
            $object->getProduct()->getId(),
            $object->getInternalMagentoProduct()->getSku()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof ProductDeleteOnChannelDTO;
    }
}
