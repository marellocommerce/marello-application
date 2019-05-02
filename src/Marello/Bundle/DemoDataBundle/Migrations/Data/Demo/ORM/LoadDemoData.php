<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDemoData extends AbstractFixture implements DependentFixtureInterface
{

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrderData::class
        ];
    }
}
