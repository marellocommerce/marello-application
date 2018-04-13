<?php

namespace Marello\Bundle\MageBridgeBundle\ImportExport\Writer;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\IntegrationBundle\ImportExport\Writer\PersistentBatchWriter;

use Marello\Bundle\MageBridgeBundle\OAuth\ResourceOwner\MagentoResourceOwner;

class AbstractWriter extends PersistentBatchWriter
{
    /** @var MagentoResourceOwner */
    protected $magentoResourceOwner;

    /**
     * @param MagentoResourceOwner $transport
     * @return $this
     */
    public function setMagentoResourceOwner(MagentoResourceOwner $transport)
    {
        $this->magentoResourceOwner = $transport;

        return $this;
    }

    public function getChannel()
    {
        $channelId = $this->getContextOption('mage_channel_id');

        $channel = $this->getEm()
            ->getRepository('OroIntegrationBundle:Channel')
            ->getOrLoadById($channelId);

        return $channel;
    }

    /**
     * @return $this
     */
    public function initTransport()
    {
        if (!$this->magentoResourceOwner->getIntegrationChannel()) {
            $transport = $this->getChannel()->getTransport();

            $this->magentoResourceOwner->setIntegrationChannel($transport)->configureCredentials();
        }
        return $this;
    }

    /**
     * @param $code
     * @return mixed
     */
    protected function getContextOption($code)
    {
        $context = $this->contextRegistry
            ->getByStepExecution($this->stepExecution);

        return $context->getValue($code);
    }

    /**
     * @return EntityManager
     */
    private function getEm()
    {
        return $this->registry->getManager();
    }
}
