<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\SupplierBundle\Entity\Supplier;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

class LoadSupplierData extends AbstractFixture
{
    const SUPPLIER_COST_PERCENTAGE = 0.40;

    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array $data
     */
    protected $data = [
        [
            'name' => 'Quiksilver',
            'priority' => 1,
            'can_dropship' => true,
            'is_active' => true,
            'address'=>
                [
                    'street_address' => 'Torenallee 20',
                    'zipcode' => '5617 BC',
                    'city'=> 'Eindhoven',
                    'country'=> 'NL',
                    'state' => 'NB'
                ],
            'email' => 'supplier1@email.com'
        ]
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
        $i = 0;

        foreach ($this->data as $values) {
            $supplier = new Supplier();
            $supplier->setName($values['name']);
            $supplier->setPriority($values['priority']);
            $supplier->setCanDropship($values['can_dropship']);
            $supplier->setIsActive($values['is_active']);
            $supplier->setEmail($values['email']);

            $address = new MarelloAddress();
            $address->setStreet($values['address']['street_address']);
            $address->setPostalCode($values['address']['zipcode']);
            $address->setCity($values['address']['city']);
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
            $organization = $this->manager
                ->getRepository(Organization::class)
                ->getFirst();
            $supplier->setOrganization($organization);
            $supplier->setAddress($address);
            $this->manager->persist($supplier);
            $this->setReference('marello_supplier_' . $i, $supplier);
            $i++;
        }

        $this->manager->flush();
    }
}
