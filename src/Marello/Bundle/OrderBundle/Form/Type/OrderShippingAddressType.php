<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\Order;

class OrderShippingAddressType extends AbstractOrderAddressType
{
    const BLOCK_PREFIX = 'marello_order_shipping_address';

    /**
     * {@inheritdoc}
     */
    protected function getAddresses(Order $entity)
    {
        return $this->customerAddressProvider->getCustomerShippingAddresses($entity->getCustomer());
    }
}
