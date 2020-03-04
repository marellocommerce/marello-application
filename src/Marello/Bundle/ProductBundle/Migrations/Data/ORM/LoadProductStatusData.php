<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\ProductBundle\Entity\ProductStatus;

class LoadProductStatusData extends AbstractFixture
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * @var array
     */
    protected $data = [
        'disabled' => 'Disabled',
        'enabled'  => 'Enabled',
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadProductStatuses();
    }

    /**
     * load and create product statuses
     */
    public function loadProductStatuses()
    {
        foreach ($this->data as $name => $label) {
            $status = new ProductStatus($name);
            $status->setLabel($label);
            $this->manager->persist($status);
            $this->setReference('product_status_'.$name, $status);
        }

        $this->manager->flush();
    }
}
