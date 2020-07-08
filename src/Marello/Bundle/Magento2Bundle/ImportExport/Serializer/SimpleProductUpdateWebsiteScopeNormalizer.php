<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Serializer;

use Marello\Bundle\Magento2Bundle\DTO\ProductSimpleUpdateWebsiteScopeDTO;
use Marello\Bundle\Magento2Bundle\ImportExport\Message\SimpleProductUpdateWebsiteScopeMessage;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class SimpleProductUpdateWebsiteScopeNormalizer implements NormalizerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param ProductSimpleUpdateWebsiteScopeDTO $object
     * @param string $format
     * @param array $context
     * @return SimpleProductUpdateWebsiteScopeMessage
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (null === $object->getWebsite()->getFirstActiveStoreCode()) {
            $this->logger->warning(
                '[Magento 2] Can\'t normalize ProductSimpleUpdateWebsiteScopeDTO object, ' .
                'because website hasn\'t active stores.',
                $object->getErrorContext()
            );

            return null;
        }

        $payload = [
            'product' => [
                'price' => $object->getDefaultPrice(),
                'custom_attributes' => [
                    'special_price' => $object->getSpecialPrice()
                ]
            ]
        ];

        /**
         * Reset value of special price from date in case if special price is not present
         */
        if (null === $object->getSpecialPrice()) {
            $payload['product']['custom_attributes']['special_from_date'] = null;
        }

        $this->addUseDefaultValueForKnownAttributesToPayload($payload);

        return SimpleProductUpdateWebsiteScopeMessage::create(
            $object->getInternalMagentoProduct()->getId(),
            $object->getProduct()->getId(),
            $object->getWebsite()->getId(),
            $object->getInternalMagentoProduct()->getSku(),
            $object->getWebsite()->getFirstActiveStoreCode(),
            $payload
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof ProductSimpleUpdateWebsiteScopeDTO;
    }

    /**
     * To prevent issue with unchecked option "Use Default Value" for known
     * attributes that has scope website or lesser, we must put information about them to the payload.
     *
     * @see https://github.com/magento/magento2/issues/9186
     * Solution found here @see https://github.com/magento/magento2/issues/9186#issuecomment-494486096
     *
     * @param array $payload
     */
    protected function addUseDefaultValueForKnownAttributesToPayload(array &$payload): void
    {
        $defaultValues = [
            'product' => [
                'status' => null,
                'name' => null,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'tax_class_id',
                        'value' => null
                    ]
                ]
            ]
        ];

        $payload = \array_merge_recursive(
            $defaultValues,
            $payload
        );
    }
}
