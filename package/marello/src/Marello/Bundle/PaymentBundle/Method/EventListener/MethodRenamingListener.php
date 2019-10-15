<?php

namespace Marello\Bundle\PaymentBundle\Method\EventListener;

use Marello\Bundle\PaymentBundle\Entity\Repository\PaymentMethodConfigRepository;
use Marello\Bundle\PaymentBundle\Method\Event\MethodRenamingEvent;

class MethodRenamingListener
{
    /**
     * @var PaymentMethodConfigRepository
     */
    private $paymentMethodConfigRepository;

    /**
     * @param PaymentMethodConfigRepository $paymentMethodConfigRepository
     */
    public function __construct(
        PaymentMethodConfigRepository $paymentMethodConfigRepository
    ) {
        $this->paymentMethodConfigRepository = $paymentMethodConfigRepository;
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
        $configs = $this->paymentMethodConfigRepository->findByMethod($oldId);
        foreach ($configs as $config) {
            $config->setMethod($newId);
        }
    }
}
