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

    /**
     * @var SalesChannel
     */
    protected $salesChannel;

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if ($context->hasOption('channel')) {
            $channelId = $context->getOption('channel');
            $this->salesChannel = $this->getSalesChannel($channelId);
            $context->setValue('salesChannel', $this->salesChannel);
        }
    }

    /**
     * @param $integrationChannelId
     * @return object
     */
    protected function getSalesChannel($integrationChannelId)
    {
        return $this->registry
            ->getRepository(SalesChannel::class)
            ->findOneBy(['integrationChannel' => $integrationChannelId]);
    }
}
