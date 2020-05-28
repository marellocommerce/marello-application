<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Serializer;

use Marello\Bundle\Magento2Bundle\DTO\ProductSimpleUpdateDTO;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;

class SimpleProductUpdateNormalizer implements NormalizerInterface
{
    /**
     * @param ProductSimpleUpdateDTO $object
     * @param string $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $websiteIds = \array_values(
            \array_map(function (Website $website) {
                return $website->getOriginId();
            }, $object->getWebsites())
        );

        $status = $object->getStatus()->getName() === ProductStatus::ENABLED ? 1 : 0;

        return [
            'productId' => $object->getMarelloProductId(),
            'sku' => $object->getSku(),
            'payload' => [
                'product' => [
                    'sku' => $object->getSku(),
                    'name' => $object->getName(),
                    'price' => $object->getPrice(),
                    'status' => $status,
                    "extension_attributes" => [
                        'website_ids' => $websiteIds
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof ProductSimpleUpdateDTO;
    }
}
