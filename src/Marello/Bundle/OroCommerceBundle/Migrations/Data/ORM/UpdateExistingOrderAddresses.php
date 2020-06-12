<?php

namespace Marello\Bundle\OroCommerceBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;

class UpdateExistingOrderAddresses extends AbstractFixture
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $orders = $this->manager
            ->getRepository(Order::class)
            ->findAll();

        foreach ($orders as $order) {
            /** @var SalesChannel $sc */
            $sc = $order->getSalesChannel();
            if ($integrationChannel = $sc->getIntegrationChannel()) {
                if ($integrationChannel->getType() === OroCommerceChannelType::TYPE) {
                    if ($order->getBillingAddress()->getId() === $order->getShippingAddress()->getId()) {
                        $billingAddress = clone $order->getBillingAddress();
                        $shippingAddress = clone $order->getShippingAddress();
                        $order->setBillingAddress($billingAddress);
                        $order->setShippingAddress($shippingAddress);
                        $this->manager->persist($billingAddress);
                        $this->manager->persist($shippingAddress);
                        $this->manager->persist($order);
                    }
                }
            }
        }
        $this->manager->flush();
    }
}
