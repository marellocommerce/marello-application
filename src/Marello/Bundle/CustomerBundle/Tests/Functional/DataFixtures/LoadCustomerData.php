<?php

namespace Marello\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\InitialFixtureInterface;

use Marello\Bundle\CustomerBundle\Entity\Customer;
use Marello\Bundle\AddressBundle\Entity\MarelloAddress;

class LoadCustomerData extends AbstractFixture implements InitialFixtureInterface
{
    /** flush manager count */
    const FLUSH_MAX = 25;

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

        $organization = $manager
            ->getRepository(Organization::class)
            ->getFirst();

        $handle = fopen($this->getDictionary('customers.csv'), "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));
                /** @var Customer $customer */
                $customer = $this->createCustomer($data, $organization);
                $this->setReference('marello-customer-' . $i, $customer);
                $i++;
            }
            $this->closeFiles($handle);
        }
        $this->manager->flush();
    }

    /**
     * Close all open files.
     * @param resource $handle
     */
    protected function closeFiles($handle)
    {
        if ($handle) {
            fclose($handle);
        }
    }

    /**
     * @param array        $row
     * @param Organization $organization
     *
     * @return Customer
     */
    protected function createCustomer($row, Organization $organization)
    {
        $billingAddress = new MarelloAddress();
        $billingAddress->setNamePrefix($row['title']);
        $billingAddress->setFirstName($row['firstname']);
        $billingAddress->setLastName($row['lastname']);
        $billingAddress->setStreet($row['street_address']);
        $billingAddress->setPostalCode($row['zipcode']);
        $billingAddress->setCity($row['city']);
        $billingAddress->setCountry(
            $this->manager
                ->getRepository('OroAddressBundle:Country')->find($row['country'])
        );
        $billingAddress->setRegion(
            $this->manager
                ->getRepository('OroAddressBundle:Region')
                ->findOneBy(['combinedCode' => $row['country'] . '-' . $row['state']])
        );
        $billingAddress->setPhone($row['telephone_number']);
        $billingAddress->setCompany($row['company']);
        $this->manager->persist($billingAddress);

        $customer = Customer::create($row['firstname'], $row['lastname'], $row['email'], $billingAddress);
        $customer->setOrganization($organization);
        $this->manager->persist($customer);
        
        return $customer;
    }

    /**
     * Get dictionary file by name
     * @param $name
     * @return string
     */
    protected function getDictionary($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
    }
}
