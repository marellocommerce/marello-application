<?php

namespace Marello\Bundle\InventoryBundle\Event;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;
use Marello\Bundle\WebhookBundle\Event\AbstractWebhookEvent;

class InventoryUpdateWebhookEvent extends AbstractWebhookEvent
{
    public function __construct($data = null)
    {
        if ($data && !$data instanceof InventoryUpdateContext) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid argument. Instance of %s expected, got %s',
                InventoryUpdateContext::class,
                is_object($data) ? get_class($data) : gettype($data)
            ));
        }

        parent::__construct($data);
    }

    public static function getName(): string
    {
        return 'marello_inventory.inventory.update';
    }

    public static function getLabel(): string
    {
        return 'Inventory Update';
    }

    protected function getContextData(): array
    {
        $product = $this->data->getProduct() ?? $this->data->getInventoryItem()->getProduct();
        $dateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        return [
            'prev_inventory_level_qty' => $this->data->getInventoryLevel() ? $this->data->getInventoryLevel()->getInventoryQty() : null,
            'inventory_adjustment' => $this->data->getInventory(),
            'inventory_level_qty' => $this->data->getInventoryLevel() ? ($this->data->getInventoryLevel()->getInventoryQty() + $this->data->getInventory()) : null,
            'change_trigger' => $this->data->getChangeTrigger(),
            'sku' => $product->getSku(),
            'updated_at' => $dateTime->format('Y-m-d H:i:s'),
            'warehouse' => $this->data->getInventoryLevel() ? $this->data->getInventoryLevel()->getWarehouse()->getCode() : null,
        ];
    }
}
