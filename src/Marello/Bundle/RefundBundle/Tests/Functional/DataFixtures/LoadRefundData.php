<?php

namespace Marello\Bundle\RefundBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Marello\Bundle\RefundBundle\Entity\Refund;
use Marello\Bundle\RefundBundle\Entity\RefundItem;

class LoadRefundData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrderData::class
        ];
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $orders = [
            $this->getReference('marello_order_0'),
            $this->getReference('marello_order_1')
        ];

        foreach ($orders as $refKey => $order) {
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
            $this->setReference(sprintf('marello_refund_%s', $refKey), $refund);
        }

        $manager->flush();
    }
}
