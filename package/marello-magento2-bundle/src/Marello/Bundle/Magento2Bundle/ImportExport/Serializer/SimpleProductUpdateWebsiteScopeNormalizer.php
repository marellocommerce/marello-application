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
}
