<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;

class LoadOrderItemData extends AbstractFixture implements DependentFixtureInterface
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
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadOrdersItems();
    }

    /**
     * load orders
     */
    public function loadOrdersItems()
    {
        $handle  = fopen($this->getDictionary('order_items.csv'), "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));

                $this->createOrderItems($data);
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
     * create new order items and related entities
     * @param array $orderItem
     */
    private function createOrderItems($orderItem)
    {
        $order = $this->getRepository('MarelloOrderBundle:Order')
            ->createQueryBuilder('o')
            ->where('o.orderNumber LIKE :orderId')
            ->setParameter('orderId', $orderItem['order_number'])
            ->getQuery()
            ->getSingleResult();

        $productResult = $this->getRepository('MarelloProductBundle:Product')->findBySku($orderItem['sku']);
        if (is_array($productResult)) {
            /** @var \Marello\Bundle\ProductBundle\Entity\Product $product */
            $product = array_shift($productResult);
        }
        $itemEntity = new OrderItem();
        $itemEntity->setProduct($product);
        $itemEntity->setOrder($order);
        $itemEntity->setQuantity($orderItem['qty']);
        $itemEntity->setPrice($orderItem['price']);
        $itemEntity->setTotalPrice($orderItem['total_price']);
        $itemEntity->setTax($orderItem['tax']);

        $order->addItem($itemEntity);

        // accumulate the totals for order
        $subtotal = $order->getSubtotal() + $itemEntity->getTotalPrice();
        $tax = $order->getTotalTax() + $itemEntity->getTax();
        $total = $order->getGrandTotal() + $itemEntity->getTotalPrice();

        $order
            ->setSubtotal($subtotal)
            ->setTotalTax($tax)
            ->setGrandTotal($total);

        $this->manager->persist($order);
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
