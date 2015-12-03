<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\AddressBundle\Entity\Address;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class LoadOrderData extends AbstractFixture implements DependentFixtureInterface
{

    /**
     * {@inheritdoc}
     */
    function getDependencies()
    {
        return [
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSalesData',
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $orders = $this->loadOrders($manager);
        $this->loadOrderItems($manager, $orders);

        $manager->flush();
    }

    public function loadOrders(ObjectManager $manager)
    {
        $handle = fopen($this->loadDictionary('customers.csv'), 'r');
        if ($handle) {
            $headers = array();
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));

                $this->createOrder($data, $manager);
                $i++;
            }
            fclose($handle);
        }

    }

    protected function createOrder(array $order, $manager)
    {
        $billing = new Address();
        $billing->setNamePrefix($order['title']);
        $billing->setFirstName($order['firstname']);
        $billing->setLastName($order['lastname']);
        $billing->setStreet($order['street_address']);
        $billing->setPostalCode($order['zipcode']);
        $billing->setCity($order['city']);
        $billing->setCountry(
            $manager->getRepository('OroAddressBundle:Country')->find($order['country'])
        );
        $billing->setRegion(
            $manager->getRepository('OroAddressBundle:Region')->findOneBy(['code' => $order['state']])
        );
        $billing->setCompany($order['company']);
        $billing->setPhone($order['telephone_number']);
        $billing->setEmail($order['email']);
        $shipping = clone $billing;

        $orderEntity = new Order($billing, $shipping);

        $chNo = rand(0,1);
        $orderEntity->setSalesChannel($this->getReference('marello_sales_channel_' . $chNo));
        $this->loadOrderItems($manager,$orderEntity);
    }

    /**
     * @param ObjectManager $manager
     * @param Order[]       $orders
     */
    private function loadOrderItems(ObjectManager $manager, array $orders)
    {

        foreach ($orders as $order) {
            $subtotal = 0;
            $tax      = 0;
            $total    = 0;

            foreach ($items as $item) {

                $itemEntity = new OrderItem();

                foreach ($item as $attribute => $value) {
                    call_user_func([$itemEntity, 'set' . $attribute], $value);
                    $order->addItem($itemEntity);
                }

                $subtotal += $itemEntity->getPrice();
                $tax += $itemEntity->getTax();
                $total += $itemEntity->getTotalPrice();
            }
            $order
                ->setSubtotal($subtotal)
                ->setTotalTax($tax)
                ->setGrandTotal($total);

            $manager->persist($order);
        }
    }

    protected function loadDictionary($name)
    {
        static $dictionaries = array();

        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@MarelloDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');

        if (!isset($dictionaries[$name])) {
            $dictionary = array();
            $fileName = $dictionaryDir . DIRECTORY_SEPARATOR . $name;
            foreach (file($fileName) as $item) {
                $dictionary[] = trim($item);
            }
            $dictionaries[$name] = $dictionary;
        }

        return $dictionaries[$name];
    }

    /**
     * Generates an email
     *
     * @param  string $firstName
     * @param  string $lastName
     * @return string
     */
    private function generateEmail($firstName, $lastName)
    {
        $uniqueString = substr(uniqid(rand()), -5, 5);
        $domains = array('yahoo.com', 'gmail.com', 'example.com', 'hotmail.com', 'aol.com', 'msn.com');
        $randomIndex = rand(0, count($domains) - 1);
        $domain = $domains[$randomIndex];

        return sprintf("%s.%s_%s@%s", strtolower($firstName), strtolower($lastName), $uniqueString, $domain);
    }

    /**
     * Generate a first name
     *
     * @return string
     */
    private function generateFirstName()
    {
        $firstNamesDictionary = $this->loadDictionary('first_names.txt');
        $randomIndex = rand(0, count($firstNamesDictionary) - 1);

        return trim($firstNamesDictionary[$randomIndex]);
    }

    /**
     * Generates a last name
     *
     * @return string
     */
    private function generateLastName()
    {
        $lastNamesDictionary = $this->loadDictionary('last_names.txt');
        $randomIndex = rand(0, count($lastNamesDictionary) - 1);

        return trim($lastNamesDictionary[$randomIndex]);
    }
}
