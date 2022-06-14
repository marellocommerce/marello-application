<?php

namespace Marello\Bundle\InventoryBundle\Model\Allocation\Notifier;

use Marello\Bundle\InventoryBundle\Entity\Allocation;
use Marello\Bundle\NotificationBundle\Email\SendProcessor;
use Marello\Bundle\InventoryBundle\Model\Allocation\WarehouseNotifierInterface;

class WarehouseEmailNotifier implements WarehouseNotifierInterface
{
    const IDENTIFIER = 'email_notifier';
    const LABEL = 'marello.inventory.warehouse_notifier.email';
    const TEMPLATE = 'marello_warehouse_allocation';

    /**
     * @var SendProcessor
     */
    private $notificationProcessor;

    /**
     * EmailNotifier constructor.
     * @param SendProcessor $notificationProcessor
     */
    public function __construct(
        SendProcessor $notificationProcessor
    ) {
        $this->notificationProcessor = $notificationProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return self::LABEL;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return true;
    }

    public function notifyWarehouse(Allocation $allocation)
    {
        $template   = self::TEMPLATE;
        $recipients = $allocation->getWarehouse()->getEmail();

        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $this->notificationProcessor->sendNotification($template, $recipients, $allocation, []);
    }
}
