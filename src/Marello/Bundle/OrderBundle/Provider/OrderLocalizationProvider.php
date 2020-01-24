<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\LocaleBundle\Model\LocaleAwareInterface;
use Marello\Bundle\LocaleBundle\Provider\EntityLocalizationProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderAwareInterface;

class OrderLocalizationProvider implements EntityLocalizationProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getLocalization(LocaleAwareInterface $entity)
    {
        if ($entity instanceof Order) {
            return $entity->getLocalization();
        } elseif ($entity instanceof OrderAwareInterface) {
            if ($order = $entity->getOrder()) {
                return $order->getLocalization();
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(LocaleAwareInterface $entity)
    {
        if ($entity instanceof Order || $entity instanceof OrderAwareInterface) {
            return true;
        }
        return false;
    }
}
