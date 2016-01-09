<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\ReturnBundle\Entity\ReturnItem;

class LoadReturnData extends AbstractFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            'Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData',
        ];
    }

    public function load(ObjectManager $manager)
    {
        $orders = $manager->getRepository('MarelloOrderBundle:Order')->findAll();

        foreach ($orders as $order) {
            if (rand(0, 3) !== 0) {
                continue;
            }

            $return = new ReturnEntity();

            $return->setOrder($order);

            $order->getItems()->map(function (OrderItem $item) use ($return) {
                $returnItem = new ReturnItem($item);
                $returnItem->setQuantity(rand(1, $item->getQuantity()));

                $return->addReturnItem($returnItem);
            });

            $manager->persist($return);
        }

        $manager->flush();
    }
}
