<?php

namespace Marello\Bundle\SubscriptionBundle\Form\Type;

use Marello\Bundle\SubscriptionBundle\Entity\Subscription;

class SubscriptionBillingAddressType extends AbstractSubscriptionAddressType
{
    const BLOCK_PREFIX = 'marello_subscription_billing_address';

    /**
     * {@inheritdoc}
     */
    protected function getAddresses(Subscription $entity)
    {
        return $this->customerAddressProvider->getCustomerBillingAddresses($entity->getCustomer());
    }
}
