<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Serializer;

use Marello\Bundle\Magento2Bundle\DTO\ProductSimpleUpdateDTO;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\Magento2Bundle\ImportExport\Message\SimpleProductUpdateMessage;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;

class SimpleProductUpdateNormalizer implements NormalizerInterface
{
    /**
     * @param ProductSimpleUpdateDTO $object
     * @param string $format
     * @param array $context
     * @return SimpleProductUpdateMessage
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $websiteIds = \array_values(
            \array_map(function (Website $website) {
                return $website->getOriginId();
            }, $object->getWebsites())
        );

        $isBackorderAllowed = $object->getInventoryItem()->isBackorderAllowed() ? 1 : 0;
        $status = $object->getStatus()->getName() === ProductStatus::ENABLED ? 1 : 2;
        $inventoryQty = $object->getBalancedInventoryLevel() ?
            $object->getBalancedInventoryLevel()->getInventoryQty() :
            null;

        $originId = $object->getInternalMagentoProduct()->getOriginId();
        $internalProductId = $object->getInternalMagentoProduct()->getId();
        $productId = $object->getProduct()->getId();
        $currentSku = $object->getProduct()->getSku();
        $name = (string) $object->getProduct()->getDefaultName();

        $payload = [
            'saveOptions' => 'true',
            'product' => [
                'id' => $originId,
                'sku' => $currentSku,
                'name' => $name,
                'status' => $status,
                "extension_attributes" => [
                    'website_ids' => $websiteIds,
                    'stock_item' => [
                        'use_config_backorders' => false,
                        'backorders' => $isBackorderAllowed,
                        'qty' => $inventoryQty
                    ]
                ]
            ]
        ];

        return SimpleProductUpdateMessage::create(
            $internalProductId,
            $productId,
            $object->getInternalMagentoProduct()->getSku(),
            $payload
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof ProductSimpleUpdateDTO;
    }
}
