<?php

namespace Marello\Bundle\SalesBundle\Provider;

use Marello\Bundle\LocaleBundle\Model\LocaleAwareInterface;
use Marello\Bundle\LocaleBundle\Provider\EntityLocalizationProviderInterface;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderAwareInterface;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class SalesChannelLocalizationProvider implements EntityLocalizationProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getLocalization(LocaleAwareInterface $entity)
    {
        if ($entity instanceof SalesChannel) {
            return $entity->getLocalization();
        } elseif ($entity instanceof Order) {
            if ($salesChannel = $entity->getSalesChannel()) {
                return $salesChannel->getLocalization();
            }
        } elseif ($entity instanceof OrderAwareInterface) {
            if ($order = $entity->getOrder()) {
                if ($salesChannel = $order->getSalesChannel()) {
                    return $salesChannel->getLocalization();
                }
            }
        }
        
        return null;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(LocaleAwareInterface $entity)
    {
        if ($entity instanceof SalesChannel || $entity instanceof Order || $entity instanceof OrderAwareInterface) {
            return true;
        }
        return false;
    }
}
