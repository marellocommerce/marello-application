<?php

namespace Marello\Bundle\TaskBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadTaskTypeData extends AbstractFixture
{
    public const TASK_TYPE_GENERAL = 'task_type_general';
    public const TASK_TYPE_ALLOCATION = 'task_type_allocation';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager)
    {
        $typeClass = ExtendHelper::buildEnumValueClassName('task_type');
        $generalType = $manager->find($typeClass, 'general');
        $allocationType = $manager->find($typeClass, 'allocation');

        $this->setReference(self::TASK_TYPE_GENERAL, $generalType);
        $this->setReference(self::TASK_TYPE_ALLOCATION, $allocationType);
    }
}
