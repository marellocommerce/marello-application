<?php

namespace Marello\Bundle\UPSBundle\Event;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Symfony\Component\EventDispatcher\Event;

class MethodTypesBeforeCreateEvent extends Event
{
    const NAME = 'marello_ups.shipping_method_types_create.before';

    /**
     * @var Channel
     */
    protected $channel;
    
    /**
     * @var array
     */
    protected $shippingServices = [];

    /**
     * @param Channel $channel
     * @param array $shippingServices
     */
    public function __construct(Channel $channel, array $shippingServices = [])
    {
        $this->channel = $channel;
        $this->shippingServices = $shippingServices;
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     */
    public function setChannel(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return array
     */
    public function getShippingServices()
    {
        return $this->shippingServices;
    }

    /**
     * @param array $shippingServices
     */
    public function setShippingServices(array $shippingServices = [])
    {
        $this->shippingServices = $shippingServices;
    }
}
