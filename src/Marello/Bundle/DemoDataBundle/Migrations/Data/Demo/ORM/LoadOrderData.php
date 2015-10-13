<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Entity\TypedAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;

class LoadOrderData extends AbstractFixture
{

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
        $orders = [
            [
                'OrderReference' => 123456,
                'Subtotal'       => 100,
                'TotalTax'       => 20,
            ],
            [
                'Subtotal' => 100,
                'TotalTax' => 20,
            ],
            [
                'Subtotal' => 1000,
                'TotalTax' => 100,
            ],
            [
                'OrderReference' => 5674321,
                'Subtotal'       => 799,
                'TotalTax'       => 199,
            ],
        ];

        $orderEntities = [];

        foreach ($orders as $order) {
            $billing = new TypedAddress();
            $billing->setFirstName('Falco');
            $billing->setLastName('van der Maden');
            $billing->setStreet('Torenallee 20');
            $billing->setPostalCode('5617 BC');
            $billing->setCity('Eindhoven');
            $billing->setCountry(
                $manager->getRepository('OroAddressBundle:Country')->find('NL')
            );
            $billing->setPhone('+31 40 7074808');
            $billing->setEmail('falco@madia.nl');

            $shipping = clone $billing;

            $billing->getTypes()->add(
                $manager->getRepository('OroAddressBundle:AddressType')->find(AddressType::TYPE_BILLING)
            );
            $shipping->getTypes()->add(
                $manager->getRepository('OroAddressBundle:AddressType')->find(AddressType::TYPE_SHIPPING)
            );

            $orderEntity = new Order($billing, $shipping);
            $total       = 0;

            foreach ($order as $attribute => $value) {
                call_user_func([$orderEntity, 'set' . $attribute], $value);
                if ($attribute === 'Subtotal' | $attribute === 'TotalTax') {
                    $total += $value;
                }
            }
            $orderEntities[] = $orderEntity->setGrandTotal($total);
        }

        return $orderEntities;
    }

    /**
     * @param ObjectManager $manager
     * @param Order[]       $orders
     */
    private function loadOrderItems(ObjectManager $manager, array $orders)
    {
        $items = [
            [
                'Sku'        => 'HSTUC',
                'Name'       => 'Classic Unisex Scrubs Top',
                'Quantity'   => 10,
                'Price'      => 29.99,
                'Tax'        => 5,
                'TotalPrice' => '34.99',
            ],
            [
                'Sku'        => 'HSSUC',
                'Name'       => 'Classic Unisex Scrub Set',
                'Quantity'   => 2,
                'Price'      => 29.99,
                'Tax'        => 5,
                'TotalPrice' => '34.99',
            ],
            [
                'Sku'        => 'HSSUC3',
                'Name'       => 'Custom logo patch',
                'Quantity'   => 5,
                'Price'      => 21.18,
                'Tax'        => 5,
                'TotalPrice' => '36.18',
            ],
            [
                'Sku'        => 'HSSUC5',
                'Name'       => 'Custom embroidery',
                'Quantity'   => 7,
                'Price'      => 29.99,
                'Tax'        => 5,
                'TotalPrice' => '34.99',
            ],
            [
                'Sku'        => 'HCCM',
                'Name'       => 'Men\'s Counter Coat',
                'Quantity'   => 2,
                'Price'      => 36.99,
                'Tax'        => 5,
                'TotalPrice' => '41.99',
            ],
            [
                'Sku'        => 'HCCMR',
                'Name'       => 'Men\'s Counter Coat red',
                'Quantity'   => 13,
                'Price'      => 34.49,
                'Tax'        => 5,
                'TotalPrice' => '39.99',
            ],
        ];

        foreach ($orders as $order) {
            foreach ($items as $item) {

                $itemEntity = new OrderItem();

                foreach ($item as $attribute => $value) {
                    call_user_func([$itemEntity, 'set' . $attribute], $value);
                    $order->addItem($itemEntity);
                }
            }
            $manager->persist($order);
        }
    }
}
