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
    /** default tax percentage constant */
    const DEFAULT_TAX_PERCENTAGE = 21;

    /** flush manager count */
    const FLUSH_MAX = 50;

    /** @var ObjectManager $manager */
    protected $manager;

    /** @var array $data */
    protected $data = [
        'Magento Store'           => '10',
        'Flagship Store New York' => '20',
        'Store Washington D.C.'   => '30',
        'HQ'                      => '40',
    ];

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
        $handle  = fopen($this->getDictionary('orderAddresses.csv'), "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ";")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $data = array_combine($headers, array_values($data));

                $order = $this->createOrder($data);
                if (!$i) {
                    $this->setReference('marello_order_first', $order);
                }
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
     * @param array $order
     *
     * @return Order
     */
    protected function createOrder(array $order)
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
            $this->getRepository('OroAddressBundle:Region')->findOneBy(['code' => $order['state']])
        );
        $billing->setPhone($order['telephone_number']);
        $billing->setEmail($order['email']);
        $shipping = clone $billing;

        $orderEntity = new Order($billing, $shipping);
        $chNo = rand(0, 3);
        $channel = $this->getReference('marello_sales_channel_' . $chNo);
        $orderEntity->setSalesChannel($channel);
        $setReferenceNumber = (rand(0, 100) % 2 == 0) ? true : false;
        if ($setReferenceNumber) {
            $min = (int) ($this->data[$channel->getName()] . '0000000');
            $max = $min + rand(1, 1000);
            $orderEntity->setOrderReference(rand($min, $max));
        }

        $this->loadOrderItems($orderEntity);

        return $orderEntity;
    }

    /**
     * create new order items and related entities
     * @param Order $order
     */
    private function loadOrderItems($order)
    {
        $randItemsCount = rand(1, 4);
        $tax = $subtotal = $total = 0;
        $productRp = $this->getRepository('MarelloProductBundle:Product');
        for ($i = 0; $i < $randItemsCount; $i++) {
            $randQty = rand(1, 3);
            $channel = $order->getSalesChannel();
            $channelId = $channel->getId();
            $product = $this->getRandomProduct($channel, $productRp);
            if (count($product) === 0) {
                $range = $this->getMinMaxProductId($productRp);
                $product = $productRp->find(rand($range[1], $range[2]));
            }

            if (is_array($product)) {
                $product = array_shift($product);
            }

            $unitPrice = $product->getPrices()->filter(function($price) use ($channelId) {
                return $price->getChannel()->getId() === $channelId;
            });

            $itemBasePrice = $product->getPrice();
            if (count($unitPrice) > 0) {
                $itemBasePrice = $unitPrice->first()->getValue();
            }

            $itemEntity = new OrderItem();
            $itemEntity->setProduct($product);
            $itemEntity->setOrder($order);
            $itemEntity->setQuantity($randQty);
            $itemEntity->setPrice($itemBasePrice);

            // calculate total price (qty * price)
            $itemEntity->setTotalPrice(($itemBasePrice * $randQty));
            // calculate tax
            $priceExcl = (($itemEntity->getTotalPrice() / (self::DEFAULT_TAX_PERCENTAGE + 100)) * 100);
            $itemTax = ($itemEntity->getTotalPrice() - $priceExcl);
            $itemEntity->setTax($itemTax);

            // accumulate the totals for order
            $subtotal += $itemEntity->getTotalPrice();
            $tax += $itemEntity->getTax();
            $total += $itemEntity->getTotalPrice();
            $order->addItem($itemEntity);
        }

        $order
            ->setSubtotal($subtotal)
            ->setTotalTax($tax)
            ->setGrandTotal($total);

        $this->manager->persist($order);
    }

    /**
     * Get random product based on SalesChannel from order
     *
     * @param \Marello\Bundle\SalesBundle\Entity\SalesChannel $channel
     * @param \Doctrine\Common\Persistence\ObjectRepository $repo
     *
     * @return mixed
     */
    protected function getRandomProduct($channel, $repo)
    {
        $count = $this->getProductCount($repo);
        $qb = $repo->createQueryBuilder('p');

        return $qb
            ->where(
                $qb->expr()->isMemberOf(':salesChannel', 'p.channels')
            )
            ->setFirstResult(rand(0, $count - 1))
            ->setMaxResults(1)
            ->setParameter('salesChannel', $channel)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get product count
     * @param \Doctrine\Common\Persistence\ObjectRepository $repo
     * @return mixed
     */
    protected function getProductCount($repo)
    {
        return $repo->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get the min and max ID for product
     * @param \Doctrine\Common\Persistence\ObjectRepository $repo
     * @return mixed
     */
    protected function getMinMaxProductId($repo)
    {
        return $repo->createQueryBuilder('p')
            ->select('MIN(p), MAX(p)')
            ->getQuery()
            ->getSingleResult();
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
