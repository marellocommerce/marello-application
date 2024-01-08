<?php

namespace Marello\Bundle\POSBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Marello\Bundle\SalesBundle\Entity\SalesChannelType;

class LoadSalesChannelPOSTypeData extends AbstractFixture
{
    const POS = 'pos';

    /** @var array */
    protected $data = [
        self::POS => 'Pos'
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $name => $label) {
            $existingType = $manager->getRepository(SalesChannelType::class)->find($name);
            if (!$existingType) {
                $scType = new SalesChannelType($name);
                $scType->setLabel($label);
                $manager->persist($scType);
            }
        }

        $manager->flush();
    }
}
