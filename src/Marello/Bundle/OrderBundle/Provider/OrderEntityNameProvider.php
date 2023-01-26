<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

use Marello\Bundle\OrderBundle\Entity\Order;

class OrderEntityNameProvider implements EntityNameProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName($format, $locale, $entity)
    {
        if ($format === EntityNameProviderInterface::FULL && is_a($entity, Order::class)) {
            return sprintf('%s', $entity->getOrderNumber());
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getNameDQL($format, $locale, $className, $alias)
    {
        return false;
    }
}
