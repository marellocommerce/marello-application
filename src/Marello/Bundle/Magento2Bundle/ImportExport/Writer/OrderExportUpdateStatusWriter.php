<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Writer;

use Marello\Bundle\Magento2Bundle\ImportExport\Message\OrderStatusUpdateMessage;

class OrderExportUpdateStatusWriter extends AbstractExportWriter
{
    /**
     * @param OrderStatusUpdateMessage $item
     */
    protected function doWrite($item): void
    {
        if (!$item instanceof OrderStatusUpdateMessage) {
            $this->logger->warning(
                '[Magento 2] Given incorrect input data for writing in OrderExportUpdateStatusWriter.',
                [
                    'expected' => OrderStatusUpdateMessage::class,
                    'given' => is_object($item) ? get_class($item) : gettype($item)
                ]
            );

            return;
        }

        $this->logger->info(
            sprintf('[Magento 2] Starting update order status with ID "%s".', $item->getMarelloOrderId())
        );

        $this->getTransport()->updateOrderStatus($item->getMagentoOrderOriginId(), $item->getPayload());

        $this->logger->info(
            sprintf('[Magento 2] Order with ID "%s" was successfully updated.', $item->getMarelloOrderId())
        );
    }
}
