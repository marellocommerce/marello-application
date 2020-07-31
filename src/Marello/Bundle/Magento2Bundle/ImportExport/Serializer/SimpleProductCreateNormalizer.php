<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Serializer;

use Marello\Bundle\Magento2Bundle\DTO\ProductSimpleCreateDTO;
use Marello\Bundle\Magento2Bundle\Entity\Website;
use Marello\Bundle\Magento2Bundle\ImportExport\Message\SimpleProductCreateMessage;
use Marello\Bundle\Magento2Bundle\ImportExport\Message\SimpleProductUpdateWebsiteScopeMessage as WebsiteData;
use Marello\Bundle\ProductBundle\Entity\ProductStatus;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class SimpleProductCreateNormalizer implements NormalizerInterface
{
    /**
     * @param ProductSimpleCreateDTO $object
     * @param string $format
     * @param array $context
     * @return SimpleProductCreateMessage
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

        $productId = $object->getProduct()->getId();
        $sku = $object->getProduct()->getSku();
        $name = (string) $object->getProduct()->getDefaultName();

        $payload = [
            'product' => [
                'sku' => $sku,
                'name' => $name,
                'attribute_set_id' => $object->getAttrSetID(),
                'price' => $object->getPrice(),
                'type_id' => $object->getTypeId(),
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

        $websiteIds = \array_values(
            \array_map(function (Website $website) {
                return $website->getId();
            }, $object->getWebsites())
        );

        return SimpleProductCreateMessage::create(
            $productId,
            $payload,
            $websiteIds
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof ProductSimpleCreateDTO;
    }
}
