<?php

namespace Marello\Bundle\SubscriptionBundle\Form\Type;

use Marello\Bundle\SubscriptionBundle\Entity\Subscription;

class SubscriptionShippingAddressType extends AbstractSubscriptionAddressType
{
    const BLOCK_PREFIX = 'marello_subscription_shipping_address';

    /**
     * {@inheritdoc}
     */
    protected function getAddresses(Subscription $entity)
    {
        return $this->customerAddressProvider->getCustomerShippingAddresses($entity->getCustomer());
    }
}
