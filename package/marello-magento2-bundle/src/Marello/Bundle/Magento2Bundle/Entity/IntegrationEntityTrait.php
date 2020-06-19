<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

trait IntegrationEntityTrait
{
    /**
     * @var Integration
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $channel;

    /**
     * @param Integration $integration
     * @return IntegrationEntityTrait
     */
    public function setChannel(Integration $integration): self
    {
        $this->channel = $integration;

        return $this;
    }

    /**
     * @return Integration
     */
    public function getChannel(): Integration
    {
        return $this->channel;
    }

    /**
     * @return int
     */
    public function getChannelId(): int
    {
        return $this->channel->getId();
    }

    /**
     * @return string
     */
    public function getChannelName(): string
    {
        return $this->channel->getName();
    }
}
