<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadReturnData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadOrderData::class,
        ];
    }

    /**
     * {@inheritdoc}
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $orders = $manager->getRepository('MarelloOrderBundle:Order')->findAll();

        if (count($orders) <= 0) {
            return;
        }

        $channel = $this->getReference('marello_sales_channel_1');
        $reasonClass = ExtendHelper::buildEnumValueClassName('marello_return_reason');
        $reasons = $manager->getRepository($reasonClass)->findAll();
        $statusClass = ExtendHelper::buildEnumValueClassName('marello_return_status');
        $statuses = $manager->getRepository($statusClass)->findAll();

        $i = 0;
        foreach ($orders as $order) {
            if (rand(0, 3) !== 0) {
                if (!$this->hasReference('marello_order_unreturned')) {
                    $this->setReference('marello_order_unreturned', $order);
                }
                continue;
            }

            $return = new ReturnEntity();

            $return->setOrder($order);
            $return->setSalesChannel($channel);
            $returnReferenceNumber = $this->createReturnReferenceNumber($order, $i);
            $return->setReturnReference($returnReferenceNumber);

            $order->getItems()->map(function (OrderItem $item) use ($return, $reasons, $statuses) {
                $returnItem = new ReturnItem($item);
                $returnItem->setQuantity(rand(1, $item->getQuantity()));
                $returnItem->setReason($reasons[rand(0, count($reasons) - 1)]);
                $returnItem->setStatus($statuses[rand(0, count($statuses) - 1)]);

                $return->addReturnItem($returnItem);
            });

            $manager->persist($return);
            $this->setReference('marello_return_' . $i++, $return);
        }

        $manager->flush();
    }

    /**
     * Create the return reference number based on order number
     * @param $order
     * @param $number
     * @return string
     */
    private function createReturnReferenceNumber($order, $number)
    {
        $calculatedRefNumber = ($order->getOrderNumber() - $number);
        $referenceNumber = sprintf('%09d', $calculatedRefNumber);

        return $referenceNumber;
    }
}
