<?php

namespace Marello\Bundle\Magento2Bundle\Entity;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

trait IntegrationEntityTrait
{
    /**
     * @var Integration|null
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="SET NULL")
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
     * @return Integration|null
     */
    public function getChannel(): ?Integration
    {
        return $this->channel;
    }

    /**
     * @return int
     */
    public function getChannelId(): int
    {
        return $this->channel ? $this->channel->getId() : null;
    }

    /**
     * @return string
     */
    public function getChannelName(): string
    {
        return $this->channel ? $this->channel->getName() : null;
    }
}
