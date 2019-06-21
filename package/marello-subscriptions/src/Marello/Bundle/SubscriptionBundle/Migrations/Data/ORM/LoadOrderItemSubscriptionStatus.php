<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\OrderBundle\Migrations\Data\ORM\LoadOrderItemStatusData;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadOrderItemSubscriptionStatus extends AbstractFixture
{
    const SUBSCRIPTION = 'subscription';

    /** @var array */
    protected $data = [
        'Subscription' => false
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(LoadOrderItemStatusData::ITEM_STATUS_ENUM_CLASS);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 1;
        foreach ($this->data as $name => $isDefault) {
            $enumOption = $enumRepo->createEnumValue($name, $priority++, $isDefault);
            $manager->persist($enumOption);
        }

        $manager->flush();
    }
}
