<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Marello\Bundle\AddressBundle\Entity\Address;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class LoadOrderData extends AbstractFixture
    implements DependentFixtureInterface, ContainerAwareInterface
{
    const DEFAULT_TAX_PERCENTAGE = 21;

    const FLUSH_MAX = 50;

    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    function getDependencies()
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
        $this->loadOrders($manager);
    }

    public function loadOrders(ObjectManager $manager)
    {
        $handle  = fopen($this->getDictionaryDir() . DIRECTORY_SEPARATOR . 'orderAddresses.csv', "r");
        if ($handle) {
            $headers = array();
            if (($data = fgetcsv($handle, 1000, ";")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $data = array_combine($headers, array_values($data));

                $this->createOrder($data, $manager);
                $i++;
                if ($i % self::FLUSH_MAX == 0) {
                    $manager->flush();
                }
            }
            fclose($handle);
            $manager->flush();
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
        $billing->setPhone($order['telephone_number']);
        $billing->setEmail($order['email']);
        $shipping = clone $billing;

        $orderEntity = new Order($billing, $shipping);;
        $chNo = rand(0,3);

        $orderEntity->setSalesChannel($this->getReference('marello_sales_channel_' . $chNo));
        $this->loadOrderItems($manager,$orderEntity);
    }

    /**
     * @param ObjectManager $manager
     * @param Order[]       $order
     */
    private function loadOrderItems(ObjectManager $manager, $order)
    {
        $randItemsCount = rand(1,6);
        $tax = $subtotal = $total = 0;
        for ($i = 0;$i < $randItemsCount; $i++) {
            $randQty = rand(1,5);
            $channel = $order->getSalesChannel();
            $channelId = $channel->getId();
            $product = $this->getRandomProduct($manager, $channel);
            if(count($product) === 0) {
                $range = $this->getMinAndMaxId($manager->getRepository('MarelloProductBundle:Product'));
                $product = $manager->getRepository('MarelloProductBundle:Product')->find(rand($range[1], $range[2]));
            }

            if(is_array($product)) {
                $product = array_shift($product);
            }

            $unitPrice = $product->getPrices()->filter( function($price) use ($channelId) {
                return $price->getChannel()->getId() === $channelId;
            });

            $itemBasePrice = $product->getPrice();
            if(count($unitPrice) > 0) {
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

        $manager->persist($order);
    }

    protected function getDictionaryDir()
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@MarelloDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');
    }

    /**
     * Get random product based on saleschannel from order
     * @param $manager
     * @param $channel
     * @return mixed
     */
    protected function getRandomProduct($manager, $channel)
    {
        $repo = $manager->getRepository('MarelloProductBundle:Product');
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

    protected function getProductCount($repo)
    {
        return $repo->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function getMinAndMaxId($repo)
    {
        return $repo->createQueryBuilder('p')
            ->select('MIN(p), MAX(p)')
            ->getQuery()
            ->getSingleResult();
    }
}
