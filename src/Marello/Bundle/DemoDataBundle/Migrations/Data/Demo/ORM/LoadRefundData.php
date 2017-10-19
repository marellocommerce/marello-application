<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;

class LoadRefundData extends AbstractFixture implements DependentFixtureInterface
{

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadOrderData::class,
        ];
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $orders = $manager
            ->getRepository(Order::class)
            ->findAll();

        if (count($orders) <= 0) {
            return;
        }

        foreach ($orders as $order) {
            if (mt_rand(0, 4) !== 0) {
                continue;
            }

            $refund = new Refund();
            $refund->setOrder($order);
            $refund->setCustomer($order->getCustomer());
            $refund->setOrganization($order->getOrganization());
            $refund->setCurrency($order->getCurrency());

            $refundItems = $order
                ->getItems()
                ->map(
                    function (OrderItem $item) {
                        return (new RefundItem())
                            ->setOrderItem($item)
                            ->setQuantity($item->getQuantity())
                            ->setRefundAmount($item->getRowTotalInclTax())
                            ->setBaseAmount($item->getPrice())
                            ->setName($item->getProductName());
                    }
                );

            $refundItems->add(
                (new RefundItem())
                    ->setName('Shipping Costs')
                    ->setBaseAmount(10)
                    ->setRefundAmount(10)
            );

            $refundItems->map(
                function (RefundItem $item) use ($refund) {
                    $refund->addItem($item);
                    $refund->setRefundAmount(($refund->getRefundAmount() ?: 0) + $item->getRefundAmount());
                }
            );

            $manager->persist($refund);
        }

        $manager->flush();
    }
}
