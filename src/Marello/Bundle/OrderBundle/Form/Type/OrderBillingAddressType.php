<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\Order;

class OrderBillingAddressType extends AbstractOrderAddressType
{
    const BLOCK_PREFIX = 'marello_order_billing_address';

    /**
     * {@inheritdoc}
     */
    protected function getAddresses(Order $entity)
    {
        return $this->customerAddressProvider->getCustomerBillingAddresses($entity->getCustomer());
    }
}
