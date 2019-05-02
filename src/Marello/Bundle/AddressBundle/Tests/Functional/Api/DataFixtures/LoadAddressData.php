<?php

namespace Marello\Bundle\AddressBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

class LoadAddressData extends AbstractFixture
{
    const ADDRESS_1_REF = 'marelloaddress1';
    const ADDRESS_2_REF = 'marelloaddress2';

    /** @var ObjectManager $manager */
    protected $manager;

    /** @var array $data */
    protected $data = [
        self::ADDRESS_1_REF => [
            'title'         => 'Mr.',
            'firstname'     => 'Test',
            'lastname'      => 'Person',
            'street'        => 'Single Street',
            'postalcode'    => '5445',
            'city'          => 'Everywhere',
            'country'       => 'NL',
            'region'        => 'NB',
            'company'       => 'Acme',
            'phone'         => '4242424242'
        ],
        self::ADDRESS_2_REF => [
            'title'         => 'Ms.',
            'firstname'     => 'Testa',
            'lastname'      => 'Persona',
            'street'        => 'street1',
            'postalcode'    => '45678',
            'city'          => 'somewhere',
            'country'       => 'US',
            'region'        => 'NY',
            'company'       => 'Acme',
            'phone'         => '1234567890'
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadAddresses();
    }

    /**
     * load products
     */
    public function loadAddresses()
    {
        foreach ($this->data as $addressKey => $data) {
            $marelloAddress = $this->createMarelloAddress($data);
            $this->setReference($addressKey, $marelloAddress);
        }
        $this->manager->flush();
    }

    /**
     * @param array $data
     *
     * @return MarelloAddress
     */
    protected function createMarelloAddress($data)
    {
        $address = new MarelloAddress();
        $address->setNamePrefix($data['title']);
        $address->setFirstName($data['firstname']);
        $address->setLastName($data['lastname']);
        $address->setStreet($data['street']);
        $address->setPostalCode($data['postalcode']);
        $address->setCity($data['city']);
        $address->setCountry(
            $this->manager
                ->getRepository('OroAddressBundle:Country')->find($data['country'])
        );
        $address->setRegion(
            $this->manager
                ->getRepository('OroAddressBundle:Region')
                ->findOneBy(['combinedCode' => $data['country'] . '-' . $data['region']])
        );
        $address->setPhone($data['phone']);
        $address->setCompany($data['company']);
        $this->manager->persist($address);

        return $address;
    }
}
