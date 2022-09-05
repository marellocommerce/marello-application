<?php

namespace Marello\Bundle\OrderBundle\Form\Type;

use Marello\Bundle\OrderBundle\Entity\Order;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderBillingAddressType extends AbstractOrderAddressType
{
    const BLOCK_PREFIX = 'marello_order_billing_address';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'useBillingAddressAsShipping',
                CheckboxType::class,
                [
                    'label' => 'marello.order.billing_address.use_as_shipping.label',
                    'required' => false,
                    'mapped' => false,
                    'priority' => 100,
                ]
            );
    }

    protected function getAddresses(Order $entity)
    {
        return $this->customerAddressProvider->getCustomerBillingAddresses($entity->getCustomer());
    }
}
