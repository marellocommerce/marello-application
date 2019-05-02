<?php

namespace Marello\Bundle\ShippingBundle\Method\EventListener;

use Marello\Bundle\ShippingBundle\Method\Event\MethodRenamingEvent;
use Marello\Bundle\ShippingBundle\Entity\Repository\ShippingMethodConfigRepository;

class MethodRenamingListener
{
    /**
     * @var ShippingMethodConfigRepository
     */
    private $shippingMethodConfigRepository;

    /**
     * @param ShippingMethodConfigRepository $shippingMethodConfigRepository
     */
    public function __construct(ShippingMethodConfigRepository $shippingMethodConfigRepository)
    {
        $this->shippingMethodConfigRepository = $shippingMethodConfigRepository;
    }

    /**
     * @param MethodRenamingEvent $event
     */
    public function onMethodRename(MethodRenamingEvent $event)
    {
        $this->updateRuleConfigs($event->getOldMethodIdentifier(), $event->getNewMethodIdentifier());
    }

    /**
     * @param string $oldId
     * @param string $newId
     */
    private function updateRuleConfigs($oldId, $newId)
    {
        $configs = $this->shippingMethodConfigRepository->findByMethod($oldId);
        foreach ($configs as $config) {
            $config->setMethod($newId);
        }
    }
}
