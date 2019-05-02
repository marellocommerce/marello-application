<?php

namespace Marello\Bundle\PackingBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrderData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadPackingSlipData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    const COUNT = 5;

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
            LoadOrderData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $mapper = $this->container->get('marello_packing.mapper.order_to_packingslip');

        for ($i = 0; $i < self::COUNT; $i++) {
            $order = $this->getReference('marello_order_' . $i);
            $packingSlips = $mapper->map($order);

            foreach ($packingSlips as $k => $packingSlip) {
                $manager->persist($packingSlip);
                $this->setReference(sprintf('packing_slip.%d%d', $i, $k), $packingSlip);
            }
        }
        
        $manager->flush();
    }
}
