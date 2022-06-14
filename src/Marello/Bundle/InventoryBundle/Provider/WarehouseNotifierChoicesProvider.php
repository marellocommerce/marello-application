<?php

namespace Marello\Bundle\InventoryBundle\Provider;

use Symfony\Contracts\Translation\TranslatorInterface;

use Marello\Bundle\InventoryBundle\Model\Allocation\WarehouseNotifierRegistry;
use Marello\Bundle\InventoryBundle\Model\Allocation\WarehouseNotifierInterface;

class WarehouseNotifierChoicesProvider
{
    /* @var WarehouseNotifierRegistry $warehouseNotificationRegistry */
    protected $warehouseNotificationRegistry;

    /* @var TranslatorInterface $translator */
    protected $translator;

    /**
     * @param WarehouseNotifierRegistry $warehouseNotificationRegistry
     * @param TranslatorInterface $translator
     */
    public function __construct(
        WarehouseNotifierRegistry $warehouseNotificationRegistry,
        TranslatorInterface $translator
    ) {
        $this->warehouseNotificationRegistry = $warehouseNotificationRegistry;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices()
    {
        return array_reduce(
            $this->warehouseNotificationRegistry->getNotifiers(),
            function (array $result, WarehouseNotifierInterface $notifier) {
                if ($notifier->isEnabled()) {
                    $result[$notifier->getIdentifier()] = $this->translator->trans($notifier->getLabel());
                }

                return $result;
            },
            []
        );
    }
}
