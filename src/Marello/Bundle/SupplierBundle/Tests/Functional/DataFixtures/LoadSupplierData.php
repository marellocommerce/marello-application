<?php

namespace Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\SupplierBundle\Entity\Supplier;

class LoadSupplierData extends AbstractFixture
{
    const SUPPLIER_1_REF = 'supplier1';
    const SUPPLIER_2_REF = 'supplier2';
    const SUPPLIER_3_REF = 'supplier3';

    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        self::SUPPLIER_1_REF => [
            'name' => 'ActiveAndDropshipSupplier',
            'priority' => 1,
            'can_dropship' => true,
            'is_active' => true,
            'address'=> [
                'street_address' => 'Street name 1',
                'zipcode' => 12345,
                'city'=> 'Eindhoven',
                'country'=> 'NL',
                'state' => 'NB'
            ],
            'email' => 'supplier1@email.com',
            'currency' => 'USD',
            'sendBy'    => 'email'
        ],
        self::SUPPLIER_2_REF => [
            'name' => 'ActiveFalseSupplier',
            'priority' => 2,
            'can_dropship' => true,
            'is_active' => false,
            'address'=> [
                'street_address' => 'Street name 2',
                'zipcode' => 67890,
                'city'=> 'Eindhoven',
                'country'=> 'NL',
                'state'=> 'NB'
            ],
            'email' => 'supplier2@email.com',
            'currency' => 'EUR',
            'sendBy'    => 'email'
        ],
        self::SUPPLIER_3_REF => [
            'name' => 'ActiveNoDropshipSupplier',
            'priority' => 9,
            'can_dropship' => false,
            'is_active' => true,
            'address'=> [
                'street_address' => 'Street name 3',
                'zipcode' => 454545,
                'city'=> 'Eindhoven',
                'country'=> 'NL',
                'state'=> 'NB'
            ],
            'email' => 'supplier3@email.com',
            'currency' => 'USD',
            'sendBy'    => 'email'
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadSuppliers();
    }

    /**
     * load and create Suppliers
     */
    protected function loadSuppliers()
    {
        foreach ($this->data as $ref => $values) {
            $supplier = new Supplier();
            $supplier->setName($values['name']);
            $supplier->setPriority($values['priority']);
            $supplier->setCanDropship($values['can_dropship']);
            $supplier->setIsActive($values['is_active']);
            $supplier->setEmail($values['email']);
            $supplier->setCurrency($values['currency']);

            $address = new MarelloAddress();
            $address->setStreet($values['address']['street_address']);
            $address->setPostalCode($values['address']['zipcode']);
            $address->setCountry(
                $this->manager
                    ->getRepository('OroAddressBundle:Country')->find($values['address']['country'])
            );
            $address->setRegion(
                $this->manager
                    ->getRepository('OroAddressBundle:Region')
                    ->findOneBy(['combinedCode' => $values['address']['country'] . '-' . $values['address']['state']])
            );

            $supplier->setAddress($address);
            $supplier->setPoSendBy($values['sendBy']);
            $this->manager->persist($supplier);
            $this->setReference($ref, $supplier);
        }

        $this->manager->flush();
    }
}
