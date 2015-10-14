<?php

namespace Marello\Bundle\ProductBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Marello\Bundle\ProductBundle\Entity\ProductStatus;

class LoadProductStatusData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = array(
        'disabled' => 'Disabled',
        'enabled' => 'Enabled'
    );

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $name => $label) {
            $status = new ProductStatus($name);
            $status->setLabel($label);
            $manager->persist($status);
        }

        $manager->flush();
    }
}
