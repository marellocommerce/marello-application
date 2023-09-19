<?php

namespace Marello\Bundle\POSUserBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;

class LoadPOSRolesData extends AbstractFixture
{
    public const ROLE_USER = 'ROLE_POS_USER';
    public const ROLE_ADMIN = 'ROLE_POS_ADMIN';

    public function load(ObjectManager $manager)
    {
        $roleUser = new Role(self::ROLE_USER);
        $roleUser->setLabel('POS User');

        $roleSAdmin = new Role(self::ROLE_ADMIN);
        $roleSAdmin->setLabel('POS Administrator');

        $manager->persist($roleUser);
        $manager->persist($roleSAdmin);

        $manager->flush();
    }
}
