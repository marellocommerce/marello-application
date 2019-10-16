<?php

namespace Marello\Bridge\MarelloOroCommerce\UPS\EventListener;

use Marello\Bundle\UPSBundle\Entity\ShippingService;
use Marello\Bundle\UPSBundle\Event\MethodTypesBeforeCreateEvent;

class UpsShippingMethodTypesCreateBeforeEventListener
{
    /**
     * @param MethodTypesBeforeCreateEvent $event
     */
    public function onBeforeCreate(MethodTypesBeforeCreateEvent $event)
    {
        $this->convertShippingServices($event);
    }

    /**
     * @param MethodTypesBeforeCreateEvent $event
     */
    private function convertChannel(MethodTypesBeforeCreateEvent $event)
    {
        $transport = $event->getChannel()->getTransport();
    }

    /**
     * @param MethodTypesBeforeCreateEvent $event
     */
    private function convertShippingServices(MethodTypesBeforeCreateEvent $event)
    {
        $validShippingServices = [];
        foreach ($event->getShippingServices() as $shippingService) {
            if (!$shippingService instanceof ShippingService) {
                $validShippingService = new ShippingService();
                $validShippingService
                    ->setCode($shippingService->getCode())
                    ->setDescription($shippingService->getDescription())
                    ->setCountry($shippingService->getCountry());
                $validShippingServices[] = $validShippingService;
            } else {
                $validShippingServices = $shippingService;
            }
        }

        $event->setShippingServices($validShippingServices);
    }
}