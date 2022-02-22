<?php

namespace Marello\Bundle\OrderBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\CustomerBundle\Entity\Customer;

class UpdateCustomerWithShippingAddress
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->updateCustomerAddresses();
    }

    /**
     * Update existing Customers with a shippingAddress
     */
    public function updateCustomerAddresses()
    {
        $customers = $this->manager
            ->getRepository(Customer::class)
            ->findBy(['shippingAddress' => null]);


        /** @var Customer $customer */
        foreach ($customers as $customer) {
            $address = $customer->getPrimaryAddress();
            if ($address) {
                $shippingAddress = clone $address;
                $this->manager->persist($shippingAddress);
                $customer->setShippingAddress($shippingAddress);
            }
            $this->manager->persist($customer);
        }

        $this->manager->flush();
    }
}
