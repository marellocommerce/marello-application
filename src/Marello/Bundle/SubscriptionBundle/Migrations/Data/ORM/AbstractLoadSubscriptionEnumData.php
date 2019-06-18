<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

abstract class AbstractLoadSubscriptionEnumData extends AbstractFixture
{
    const ENUM_CLASS = '';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(static::ENUM_CLASS);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);
        $enumValueClassName = $enumRepo->getClassName();
        
        $priority = 1;
        $isDefault = true;
        foreach ($this->data as $id => $name) {
            $enumOption = new $enumValueClassName($id, $name, $priority++, $isDefault);
            $manager->persist($enumOption);
            $isDefault = false;
        }

        $manager->flush();
    }
}
