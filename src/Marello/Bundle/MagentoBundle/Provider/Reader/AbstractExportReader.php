<?php

namespace Marello\Bundle\MagentoBundle\Provider\Reader;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Reader\EntityReader;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;

abstract class AbstractExportReader extends EntityReader implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use EntityNameTrait;

    /**
     * @var integrationChannelId
     */
    protected $integrationChannelId;

    /**
     * @return integrationChannelId
     */
    public function getIntegrationChannelId()
    {
        return $this->integrationChannelId;
    }

    /**
     * @param $integrationChannelId
     * @return $this
     */
    public function setIntegrationChannelId($integrationChannelId)
    {
        $this->integrationChannelId = $integrationChannelId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($context->hasOption('channel')) {
            $channelId = $context->getOption('channel');
            $this->setIntegrationChannelId($channelId);
            $salesChannel = $this->getSalesChannel($channelId);
            $context->setValue('salesChannel', $salesChannel);
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
