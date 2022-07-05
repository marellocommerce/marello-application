<?php

namespace Marello\Bundle\PaymentBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class LoadPaymentStatusData extends AbstractFixture
{
    const PAYMENT_STATUS_ENUM_CLASS = 'marello_paymnt_status';
    
    const ASSIGNED = 'assigned';
    const UNASSIGNED = 'unassigned';

    /** @var array */
    protected $data = [
        'Assigned' => true,
        'Unassigned' => false,
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(self::PAYMENT_STATUS_ENUM_CLASS);

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
