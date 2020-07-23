<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Translator\Order;

use Marello\Bundle\Magento2Bundle\DTO\OrderStatusUpdateDTO;
use Marello\Bundle\Magento2Bundle\Entity\Order as MagentoOrder;
use Marello\Bundle\Magento2Bundle\ImportExport\Translator\TranslatorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class UpdateStatusTranslator implements TranslatorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param MagentoOrder $entity
     * @param array $context
     * @return OrderStatusUpdateDTO|null
     */
    public function translate($entity, array $context = [])
    {
        if (!$entity instanceof MagentoOrder) {
            $this->logger->warning(
                '[Magento 2] Input data doesn\'t fit to requirements. ' .
                'Skip to update remote order status.',
                [
                    'entity_type' => is_object($entity) ? get_class($entity) : gettype($entity),
                    'magento_order_id' => $entity instanceof MagentoOrder ? $entity->getId() : null,
                    'integration_channel_id' => $context['channel']
                ]
            );

            return null;
        }

        return new OrderStatusUpdateDTO(
            $entity,
            $entity->getInnerOrder(),
            $entity->getInnerOrder()->getOrderStatus()
        );
    }
}
