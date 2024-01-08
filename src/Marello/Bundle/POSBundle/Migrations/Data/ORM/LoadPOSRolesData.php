<?php

namespace Marello\Bundle\POSBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Oro\Bundle\UserBundle\Entity\Role;

class LoadPOSRolesData extends AbstractFixture
{
    public const ROLE_USER = 'ROLE_POS_USER';
    public const ROLE_ADMIN = 'ROLE_POS_ADMIN';

    public function load(ObjectManager $manager)
    {
        $userRole = $manager
            ->getRepository(Role::class)
            ->findOneBy(['role' => self::ROLE_USER]);
        if (!$userRole) {
            $roleUser = new Role(self::ROLE_USER);
            $roleUser->setLabel('POS User');
            $manager->persist($roleUser);
        }

        $userRoleAdmin = $manager
            ->getRepository(Role::class)
            ->findOneBy(['role' => self::ROLE_ADMIN]);
        if (!$userRoleAdmin) {
            $roleAdmin = new Role(self::ROLE_ADMIN);
            $roleAdmin->setLabel('POS Administrator');
            $manager->persist($roleAdmin);
        }

        $manager->flush();
    }
}
