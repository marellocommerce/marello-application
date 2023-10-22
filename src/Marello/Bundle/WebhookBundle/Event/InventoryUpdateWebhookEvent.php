<?php

namespace Marello\Bundle\WebhookBundle\Event;

use Marello\Bundle\InventoryBundle\Model\InventoryUpdateContext;

class InventoryUpdateWebhookEvent extends AbstractWebhookEvent
{
    public static function getName(): string
    {
        return 'marello_inventory.inventory.update';
    }

    public static function getLabel(): string
    {
        return 'Inventory Update';
    }

    public function setData($data): self
    {
        if (!$data instanceof InventoryUpdateContext) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid argument. Instance of %s expected, got %s',
                InventoryUpdateContext::class,
                is_object($data) ? get_class($data) : gettype($data)
            ));
        }

        return parent::setData($data);
    }

    protected function getContextData(): array
    {
        return [
            'inventory' => $this->data->getInventory(),
            'inventory_level_qty' => $this->data->getInventoryLevel() ? $this->data->getInventoryLevel()->getInventoryQty() : null,
            'change_trigger' => $this->data->getChangeTrigger(),
            'sku' => $this->data->getProduct()->getSku(),
            'warehouse' => $this->data->getInventoryLevel() ? $this->data->getInventoryLevel()->getWarehouse()->getCode() : null,
        ];
    }
}
