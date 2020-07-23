<?php

namespace Marello\Bundle\Magento2Bundle\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Marello\Bundle\Magento2Bundle\Batch\Step\ActionItemStep;
use Marello\Bundle\Magento2Bundle\Converter\OrderStatusIdConverterInterface;
use Marello\Bundle\Magento2Bundle\Integration\Connector\OrderConnector;
use Marello\Bundle\Magento2Bundle\Provider\TrackedSalesChannelProvider;
use Marello\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Oro\Component\DependencyInjection\ServiceLink;

class OrderStatusListener implements OptionalListenerInterface
{
    /** @var string  */
    private const ORDER_STATUS_PROPERTY_NAME = 'orderStatus';

    /** @var bool */
    private $enabled = true;

    /** @var array */
    protected $orderIdsScheduledOnOrderStatusSyncPerIntegrationIds = [];

    /** @var OrderStatusIdConverterInterface */
    protected $orderStatusIdConverter;

    /** @var TrackedSalesChannelProvider */
    protected $trackedSalesChannelProvider;

    /** @var ServiceLink */
    protected $syncScheduler;

    /**
     * @param OrderStatusIdConverterInterface $orderStatusIdConverter
     * @param TrackedSalesChannelProvider $trackedSalesChannelProvider
     * @param ServiceLink $syncScheduler
     */
    public function __construct(
        OrderStatusIdConverterInterface $orderStatusIdConverter,
        TrackedSalesChannelProvider $trackedSalesChannelProvider,
        ServiceLink $syncScheduler
    ) {
        $this->orderStatusIdConverter = $orderStatusIdConverter;
        $this->trackedSalesChannelProvider = $trackedSalesChannelProvider;
        $this->syncScheduler = $syncScheduler;
    }

    /**
     * {@inheritDoc}
     */
    public function setEnabled($enabled = true)
    {
        $this->enabled = $enabled;
    }

    /**
     * @param Order $order
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(Order $order, LifecycleEventArgs $args)
    {
        if (false === $this->enabled) {
            return;
        }

        $orderId = $order->getId();
        $changeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($order);
        if (empty($changeSet[self::ORDER_STATUS_PROPERTY_NAME]) || null === $order->getSalesChannel()) {
            return;
        }

        $integrationId = $this->trackedSalesChannelProvider->getIntegrationIdBySalesChannelId(
            $order->getSalesChannel()->getId()
        );

        if (null === $integrationId) {
            return;
        }

        [$oldValue, $newValue] = $changeSet[self::ORDER_STATUS_PROPERTY_NAME];
        if ($this->isStatusChangeRequiresSync($oldValue, $newValue)) {
            if (!\array_key_exists($integrationId, $this->orderIdsScheduledOnOrderStatusSyncPerIntegrationIds)) {
                $this->orderIdsScheduledOnOrderStatusSyncPerIntegrationIds[$integrationId] = [];
            }

            $this->orderIdsScheduledOnOrderStatusSyncPerIntegrationIds[$integrationId][$orderId] = $orderId;
        } else {
            unset($this->orderIdsScheduledOnOrderStatusSyncPerIntegrationIds[$integrationId][$orderId]);
        }
    }

    /**
     * @param AbstractEnumValue|null $oldStatus
     * @param AbstractEnumValue|null $newStatus
     * @return bool
     */
    protected function isStatusChangeRequiresSync(
        AbstractEnumValue $oldStatus = null,
        AbstractEnumValue $newStatus = null
    ): bool {
        $oldStatusId = null === $oldStatus ? null : $oldStatus->getId();
        $newStatusId = null === $newStatus ? null : $newStatus->getId();

        $oldMagentoStatusId = $this->orderStatusIdConverter->convertMarelloStatusId($oldStatusId);
        $newMagentoStatusId = $this->orderStatusIdConverter->convertMarelloStatusId($newStatusId);

        return $oldMagentoStatusId !== $newMagentoStatusId;
    }

    public function postFlush()
    {
        if (false === $this->enabled) {
            return;
        }

        foreach ($this->orderIdsScheduledOnOrderStatusSyncPerIntegrationIds as $integrationId => $orderIds) {
            if (empty($orderIds)) {
                continue;
            }

            $this->syncScheduler->getService()->schedule(
                $integrationId,
                OrderConnector::TYPE,
                [
                    'ids' => \array_values($orderIds),
                    ActionItemStep::OPTION_KEY_ACTION_NAME => OrderConnector::EXPORT_ACTION_UPDATE_ORDER_STATUS
                ]
            );
        }

        $this->orderIdsScheduledOnOrderStatusSyncPerIntegrationIds = [];
    }

    public function onClear()
    {
        $this->orderIdsScheduledOnOrderStatusSyncPerIntegrationIds = [];
    }
}
