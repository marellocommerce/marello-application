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
    /** flush manager count */
    const FLUSH_MAX = 50;

    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadSalesData',
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductData',
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadProductPricingData',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadOrders();
    }

    /**
     * load orders
     */
    public function loadOrders()
    {
        $handle  = fopen($this->getDictionary('order_data.csv'), "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ";")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $data = array_combine($headers, array_values($data));

                $this->setReference('marello_order_' . $i, $this->createOrder($data, $i));
                $i++;
                if ($i % self::FLUSH_MAX == 0) {
                    $this->manager->flush();
                }
            }
            fclose($handle);
            $this->manager->flush();
        }

    }

    /**
     * create orders and related entities
     *
     * @param array $order
     * @param int   $orderNo
     *
     * @return Order
     */
    protected function createOrder(array $order, $orderNo)
    {
        $billing = new Address();
        $billing->setNamePrefix($order['title']);
        $billing->setFirstName($order['firstname']);
        $billing->setLastName($order['lastname']);
        $billing->setStreet($order['street_address']);
        $billing->setPostalCode($order['zipcode']);
        $billing->setCity($order['city']);
        $billing->setCountry(
            $this->getRepository('OroAddressBundle:Country')->find($order['country'])
        );
        $billing->setRegion(
            $this->getRepository('OroAddressBundle:Region')->findOneBy(['combinedCode' => $order['country'] . '-' . $order['state']])
        );
        $billing->setPhone($order['telephone_number']);
        $billing->setEmail($order['email']);
        $shipping = clone $billing;

        $orderEntity = new Order($billing, $shipping);
        $channel = $this->getReference('marello_sales_channel_' . $order['channel']);
        $orderEntity->setSalesChannel($channel);
        if ($order['order_ref'] !== 'NULL') {
            $orderEntity->setOrderReference($order['order_ref']);
        }
        $orderEntity
            ->setSubtotal(0)
            ->setTotalTax(0)
            ->setGrandTotal(0)
            ->setOrderNumber(sprintf('%09d', $orderNo + 1));

        $this->manager->persist($orderEntity);

        return $orderEntity;
    }

    /**
     * Get repository for class
     * @param $className
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function getRepository($className)
    {
        return $this->manager->getRepository($className);
    }

    /**
     * Get dictionary file by name
     * @param $name
     * @return string
     */
    private function getDictionary($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
    }
}
