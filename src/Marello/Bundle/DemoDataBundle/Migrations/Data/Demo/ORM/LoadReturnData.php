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
    public function getDependencies()
    {
        return [
            LoadOrderData::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $orders = $manager->getRepository('MarelloOrderBundle:Order')->findAll();
        $channel = $this->getReference('marello_sales_channel_1');
        $reasonClass = ExtendHelper::buildEnumValueClassName('marello_return_reason');
        $reasons = $manager->getRepository($reasonClass)->findAll();

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
            $return->setReturnReference(uniqid($order->getOrderNumber()));

            $order->getItems()->map(function (OrderItem $item) use ($return, $reasons) {
                $returnItem = new ReturnItem($item);
                $returnItem->setQuantity(rand(1, $item->getQuantity()));
                $returnItem->setReason($reasons[rand(0, count($reasons) - 1)]);

                $return->addReturnItem($returnItem);
            });

            $manager->persist($return);
            $this->setReference('marello_return_' . $i++, $return);
        }

        $manager->flush();
    }
}
