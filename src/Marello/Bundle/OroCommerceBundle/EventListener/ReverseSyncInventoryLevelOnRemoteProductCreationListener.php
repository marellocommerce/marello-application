<?php

namespace Marello\Bundle\OroCommerceBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\InventoryBundle\Entity\VirtualInventoryLevel;
use Marello\Bundle\OroCommerceBundle\Event\RemoteProductCreatedEvent;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\Integration\Connector\OroCommerceInventoryLevelConnector;
use Oro\Component\DependencyInjection\ServiceLink;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractProductExportWriter;

class ReverseSyncInventoryLevelOnRemoteProductCreationListener
{
    /**
     * @var ServiceLink
     */
    private $syncScheduler;

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @param ServiceLink $syncScheduler
     * @param Registry $doctrine
     */
    public function __construct(ServiceLink $syncScheduler, Registry $doctrine)
    {
        $this->syncScheduler = $syncScheduler;
        $this->doctrine = $doctrine;
    }

    /**
     * @param RemoteProductCreatedEvent $event
     */
    public function onRemoteProductCreated(RemoteProductCreatedEvent $event)
    {
        $product = $event->getProduct();
        $data = $product->getData();
        $salesChannel = $event->getSalesChannel();
        $balancedInventory = $this->doctrine
            ->getManagerForClass(VirtualInventoryLevel::class)
            ->getRepository(VirtualInventoryLevel::class)
            ->findExistingVirtualInventory($product, $salesChannel->getGroup());
        if ($balancedInventory) {
            $integrationChannel = $salesChannel->getIntegrationChannel();
            $channelId = $integrationChannel->getId();
            if (isset($data[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD]) &&
                isset($data[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD][$channelId]) &&
                $data[AbstractProductExportWriter::INVENTORY_LEVEL_ID_FIELD][$channelId] !== null
            ) {
                if ($integrationChannel->isEnabled()) {
                    $this->syncScheduler->getService()->schedule(
                        $integrationChannel->getId(),
                        OroCommerceInventoryLevelConnector::TYPE,
                        [
                            AbstractExportWriter::ACTION_FIELD => AbstractExportWriter::UPDATE_ACTION,
                            'product' => $product->getId(),
                            'group' => $salesChannel->getGroup()->getId(),
                        ]
                    );
                }
            }
        }
    }
}