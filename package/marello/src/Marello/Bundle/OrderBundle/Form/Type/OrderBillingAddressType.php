<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\Order;

class OrderBillingAddressType extends AbstractOrderAddressType
{
    const NAME = 'marello_order_billing_address';

    protected function getAddresses(Order $entity)
    {
        return $this->orderCustomerAddressProvider->getCustomerBillingAddresses($entity->getCustomer());
    }
}
