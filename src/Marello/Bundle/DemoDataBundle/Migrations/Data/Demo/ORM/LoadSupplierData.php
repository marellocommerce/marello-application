<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\SupplierBundle\Entity\Supplier;

class LoadSupplierData extends AbstractFixture
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        ['name' => 'Supplier1', 'priority' => 1, 'can_dropship' => true, 'is_active' => true, 'address'=> ['street_address' => 'Street name 1', 'zipcode' => 12345, 'city'=> 'Eindhoven', 'country'=> 'NL', 'state' => 'NB']],
        ['name' => 'Supplier2', 'priority' => 1, 'can_dropship' => true, 'is_active' => true, 'address'=> ['street_address' => 'Street name 2', 'zipcode' => 67890, 'city'=> 'Eindhoven', 'country'=> 'NL', 'state'=> 'NB']],
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
     * load and create SalesChannels
     */
    protected function loadSuppliers()
    {
        foreach ($this->data as $values) {
            $supplier = new Supplier();
            $supplier->setName($values['name']);
            $supplier->setPriority($values['priority']);
            $supplier->setCanDropship($values['can_dropship']);
            $supplier->setIsActive($values['is_active']);

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
            $this->manager->persist($address);
            
            $supplier->setAddress($address);
            $this->manager->persist($supplier);
        }

        $this->manager->flush();
    }
}
