<?php

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

abstract class AbstractExportReader extends EntityReader implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use EntityNameTrait;
    use IntegrationChannelTrait;
    use SalesChannelTrait;

    protected $entityId;

    protected $productSku;

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($context->hasOption('channel')) {
            $channelId = $context->getOption('channel');
            $this->setIntegrationChannelId($channelId);
            $salesChannel = $this->getSalesChannel($channelId);
            $this->setSalesChannel($salesChannel);
            $context->setValue('salesChannel', $salesChannel);
        }

        if ($context->hasOption('id')) {
            $this->entityId = $context->getOption('id');
        }

        if ($context->hasOption('sku')) {
            $this->productSku = $context->getOption('sku');
        }

        $this->setSourceEntityName($this->getEntityName());

        return $this;
    }

    /**
     * @param $integrationChannelId
     * @return object
     */
    protected function getSalesChannel($integrationChannelId = null)
    {
        if (is_null($integrationChannelId)) {
            $integrationChannelId = $this->getIntegrationChannelId();
        }
        return $this->registry
            ->getRepository(SalesChannel::class)
            ->findOneBy(['integrationChannel' => $integrationChannelId]);
    }
}
