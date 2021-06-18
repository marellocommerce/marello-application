<?php

namespace Marello\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannelType;

class LoadSalesChannelTypesData extends AbstractFixture
{
    const MARELLO = 'marello';
    const STORE = 'store';

    /** @var array */
    protected $data = [
        self::MARELLO => 'Marello',
        self::STORE => 'Store'
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
