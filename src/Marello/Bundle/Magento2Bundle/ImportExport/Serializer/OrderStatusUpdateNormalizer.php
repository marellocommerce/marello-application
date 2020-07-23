<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Serializer;

use Marello\Bundle\Magento2Bundle\Converter\OrderStatusIdConverterInterface;
use Marello\Bundle\Magento2Bundle\DTO\OrderStatusUpdateDTO;
use Marello\Bundle\Magento2Bundle\ImportExport\Message\OrderStatusUpdateMessage;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;

class OrderStatusUpdateNormalizer implements NormalizerInterface
{
    /** @var string */
    protected $statusMessage = 'Status changed by Marello.';

    /** @var OrderStatusIdConverterInterface */
    protected $orderStatusIdConverter;

    /**
     * @param OrderStatusIdConverterInterface $orderStatusIdConverter
     */
    public function __construct(OrderStatusIdConverterInterface $orderStatusIdConverter)
    {
        $this->orderStatusIdConverter = $orderStatusIdConverter;
    }

    /**
     * @param OrderStatusUpdateDTO $object
     * @param string $format
     * @param array $context
     * @return OrderStatusUpdateMessage
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $magentoOrderOriginId = $object->getMagentoOrder()->getOriginId();
        $marelloOrderId = $object->getMarelloOrder()->getId();
        $magentoStatusId = $this->orderStatusIdConverter->convertMarelloStatusId(
            $object->getStatus()->getId()
        );

        $payload = [
            'statusHistory' => [
                'comment' => $this->statusMessage,
                'entity_id' => $magentoOrderOriginId,
                'entity_name' => 'order',
                'status' => $magentoStatusId
            ]
        ];

        return OrderStatusUpdateMessage::create(
            $magentoOrderOriginId,
            $marelloOrderId,
            $payload
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof OrderStatusUpdateDTO;
    }
}
