<?php

namespace Marello\Bundle\PurchaseOrderBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Marello\Bundle\SupplierBundle\Tests\Functional\DataFixtures\LoadSupplierData;

class LoadPurchaseOrderData extends AbstractFixture implements DependentFixtureInterface
{

    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadProductData::class,
            LoadSupplierData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
    }
}
