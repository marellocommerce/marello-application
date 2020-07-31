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
        $websiteOriginIds = \array_values(
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
                'extension_attributes' => [
                    'website_ids' => $websiteOriginIds,
                    'stock_item' => [
                        'use_config_backorders' => false,
                        'backorders' => $isBackorderAllowed,
                        'qty' => $inventoryQty
                    ]
                ],
                'custom_attributes' => []
            ]
        ];

        if ($object->getProductTaxClass() && $object->getProductTaxClass()->getOriginId()) {
            $payload['product']['custom_attributes'][] = [
                'attribute_code' => 'tax_class_id',
                'value' => $object->getProductTaxClass()->getOriginId()
            ];
        }

        if ($inventoryQty > 0 || $isBackorderAllowed) {
            $payload['product']['extension_attributes']['stock_item']['is_in_stock'] = true;
        }

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
