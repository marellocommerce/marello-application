<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Writer;

use Marello\Bundle\Magento2Bundle\Transport\Magento2TransportInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\ImportExport\Writer\PersistentBatchWriter;

abstract class AbstractExportWriter extends PersistentBatchWriter
{
    /** @var Magento2TransportInterface */
    private $transport;

    /** @var string */
    protected $channelClassName;

    /**
     * @param Magento2TransportInterface $transport
     */
    public function setTransport(Magento2TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param string $channelClassName
     */
    public function setChannelClassName(string $channelClassName)
    {
        $this->channelClassName = $channelClassName;
    }

    /**
     * {@inheritDoc}
     */
    public function write(array $items)
    {
        try {
            $this->getTransport()->init($this->getChannel()->getTransport());
            foreach ($items as $item) {
                $this->doWrite($item);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }
    }

    /**
     * @param $item
     * @return mixed
     */
    abstract protected function doWrite($item);

    /**
     * @return Channel
     */
    protected function getChannel(): Channel
    {
        if (!$this->getContext()->hasOption('channel')) {
            throw new \InvalidArgumentException('Channel id is missing');
        }

        $channelId = $this->getContext()->getOption('channel');
        $channel = $this->registry->getRepository($this->channelClassName)->find($channelId);

        if (!$channel) {
            throw new \InvalidArgumentException('Channel is missing');
        }

        return $channel;
    }

    /**
     * @return ContextInterface
     */
    protected function getContext()
    {
        return $this->contextRegistry->getByStepExecution($this->stepExecution);
    }

    /**
     * @return Magento2TransportInterface
     */
    protected function getTransport(): Magento2TransportInterface
    {
        if (!$this->transport) {
            throw new \InvalidArgumentException('Transport was not provided');
        }

        return $this->transport;
    }
}
