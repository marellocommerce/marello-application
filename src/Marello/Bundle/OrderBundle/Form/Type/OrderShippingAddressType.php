<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\Order;

class OrderShippingAddressType extends AbstractOrderAddressType
{
    const NAME = 'marello_order_shipping_address';

    protected function getAddresses(Order $entity)
    {
        return $this->orderCustomerAddressProvider->getCustomerShippingAddresses($entity->getCustomer());
    }
}
