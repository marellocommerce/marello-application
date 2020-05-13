<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Marello\Bundle\LocaleBundle\Model\LocalizationAwareInterface;
use Marello\Bundle\LocaleBundle\Provider\EntityLocalizationProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderAwareInterface;

class OrderLocalizationProvider implements EntityLocalizationProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getLocalization(LocalizationAwareInterface $entity)
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
    public function isApplicable(LocalizationAwareInterface $entity)
    {
        if ($entity instanceof Order || $entity instanceof OrderAwareInterface) {
            return true;
        }
        return false;
    }
}
