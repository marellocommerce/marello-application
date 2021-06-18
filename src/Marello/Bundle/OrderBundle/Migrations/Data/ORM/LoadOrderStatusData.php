<?php

namespace Marello\Bundle\OrderBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

use Marello\Bundle\OrderBundle\Model\OrderStatusesInterface;

class LoadOrderStatusData extends AbstractFixture
{
    /** @var array */
    protected $data = [
        OrderStatusesInterface::OS_PENDING => true,
        OrderStatusesInterface::OS_CANCELLED => false,
        OrderStatusesInterface::OS_INVOICED => false,
        OrderStatusesInterface::OS_PAID => false,
        OrderStatusesInterface::OS_PARTIALLY_PAID => false,
        OrderStatusesInterface::OS_PICK_AND_PACK => false,
        OrderStatusesInterface::OS_SHIPPED => false,
        OrderStatusesInterface::OS_PARTIALLY_SHIPPED => false,
        OrderStatusesInterface::OS_CLOSED => false,
        OrderStatusesInterface::OS_ON_HOLD => false,
        OrderStatusesInterface::OS_PROCESSING => false
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(OrderStatusesInterface::ORDER_STATUS_ENUM_CLASS);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        $priority = 0;
        foreach ($this->data as $name => $isDefault) {
            $enumOption = $enumRepo->createEnumValue($name, $priority++, $isDefault);
            $manager->persist($enumOption);
        }

        $manager->flush();
    }
}
