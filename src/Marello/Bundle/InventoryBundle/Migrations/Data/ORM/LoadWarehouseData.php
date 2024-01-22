<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

use Marello\Bundle\InventoryBundle\Model\Allocation\Notifier\WarehouseManualNotifier;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\InventoryBundle\Entity\Warehouse;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

class LoadWarehouseData implements FixtureInterface
{
    protected $data = [
        'country' => 'DE',
        'street' => 'Einsteinstraße',
        'street2' => '130',
        'city' => 'München',
        'state' => 'BY',
        'postalCode' => '81675',
        'phone' => '+49 89 00000',
        'company' => 'Goodwaves'
    ];
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
        $this->loadDefaultWarehouse();
    }

    protected function loadDefaultWarehouse()
    {
        /** @var Organization $organization */
        $organization = $this->manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        /*
        * Create default warehouse with name of Warehouse.
        */
        $defaultWarehouse = new Warehouse('Default Warehouse', true);
        $defaultWarehouse->setOwner($organization);
        $defaultWarehouse->setCode('default_warehouse');
        $defaultWarehouse->setNotifier(WarehouseManualNotifier::IDENTIFIER);
        $warehouseAddress = $this->createAddress($this->data);
        $defaultWarehouse->setAddress($warehouseAddress);
        $defaultWarehouse->setOrderOnDemandLocation(true);

        $this->manager->persist($defaultWarehouse);
        $this->manager->flush();
    }

    /**
     * Create Address from dummy data
     * @param array $data
     * @return MarelloAddress
     */
    private function createAddress(array $data)
    {
        $warehouseAddress = new MarelloAddress();
        $warehouseAddress->setStreet($data['street']);
        $warehouseAddress->setPostalCode($data['postalCode']);
        $warehouseAddress->setCity($data['city']);
        /** @var Country $country */
        $country = $this->manager->getRepository('OroAddressBundle:Country')->find($data['country']);
        $warehouseAddress->setCountry($country);
        /** @var Region $region */
        $region = $this->manager
            ->getRepository('OroAddressBundle:Region')
            ->findOneBy(['combinedCode' => $data['country'] . '-' . $data['state']]);
        $warehouseAddress->setRegion($region);
        $warehouseAddress->setPhone($data['phone']);
        $warehouseAddress->setCompany($data['company']);
        $this->manager->persist($warehouseAddress);

        return $warehouseAddress;
    }
}
