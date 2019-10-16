<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\OrderExportWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceOrderConnector;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Reader\EntityReaderById;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Component\Action\Event\ExtendableActionEvent;
use Oro\Component\DependencyInjection\ServiceLink;

class ReverseSyncOrderListener
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var ServiceLink
     */
    private $syncScheduler;

    /**
     * @param ManagerRegistry $registry
     * @param ServiceLink $schedulerServiceLink
     */
    public function __construct(ManagerRegistry $registry, ServiceLink $schedulerServiceLink)
    {
        $this->registry = $registry;
        $this->syncScheduler = $schedulerServiceLink;
    }

    /**
     * @param ExtendableActionEvent $event
     */
    public function onOrderCancel(ExtendableActionEvent $event)
    {
        $this->onOrderModification($event, OrderExportWriter::CANCEL_ACTION);
    }
    
    /**
     * @param ExtendableActionEvent $event
     */
    public function onOrderPaid(ExtendableActionEvent $event)
    {
        $this->onOrderModification($event, OrderExportWriter::PAID_ACTION);
    }

    /**
     * @param ExtendableActionEvent $event
     */
    public function onOrderShipped(ExtendableActionEvent $event)
    {
        $this->onOrderModification($event, OrderExportWriter::SHIPPED_ACTION);
    }

    /**
     * @param ExtendableActionEvent $event
     * @param string $action
     */
    private function onOrderModification(ExtendableActionEvent $event, $action)
    {
        if (!$this->isCorrectOrderContext($event->getContext())) {
            return;
        }

        /** @var Order $entity */
        $entity = $event->getContext()->getData()->get('order');

        if ($this->isTwoWaySyncEnabled($entity)) {
            $this->scheduleSync($entity, $action);
        }
    }

    /**
     * @param Order $entity
     * @param string $action
     */
    protected function scheduleSync(Order $entity, $action)
    {
        $integrationChannel = $this->getIntegrationChannel($entity);

        $this->syncScheduler
            ->getService()
            ->schedule(
                $integrationChannel->getId(),
                OroCommerceOrderConnector::TYPE,
                [
                    AbstractExportWriter::ACTION_FIELD => $action,
                    EntityReaderById::ID_FILTER => $entity->getId(),
                ]
            );
    }

    /**
     * @param mixed $context
     * @return bool
     */
    protected function isCorrectOrderContext($context)
    {
        return ($context instanceof WorkflowItem
            && $context->getData() instanceof WorkflowData
            && $context->getData()->has('order')
            && $context->getData()->get('order') instanceof Order
        );
    }

    /**
     * @param Order $entity
     * @return bool
     */
    protected function isTwoWaySyncEnabled(Order $entity)
    {
        $integrationChannel = $this->getIntegrationChannel($entity);
        if ($integrationChannel) {
            return $integrationChannel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled', false);
        }
        return false;
    }

    /**
     * @param Order $entity
     * @return null|Channel
     */
    protected function getIntegrationChannel(Order $entity)
    {
        $salesChannel = $entity->getSalesChannel();
        $channel = $salesChannel->getIntegrationChannel();
        if ($channel && $channel->getType() === OroCommerceChannelType::TYPE && $channel->isEnabled()) {
            return $channel;
        }

        return null;
    }
}
