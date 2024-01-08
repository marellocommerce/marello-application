<?php

namespace Marello\Bundle\POSBundle\Tests\Functional\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\AbstractFixture;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadBusinessUnit;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

use Marello\Bundle\POSBundle\Migrations\Data\ORM\LoadPOSRolesData;

class LoadUserData extends AbstractFixture implements DependentFixtureInterface
{
    public const USER_1 = 'test_user_1';
    public const USER_2 = 'test_user_2';
    public const PASSWORD = 'password';

    /**
     * @var array
     */
    protected $data = [
        self::USER_1 => [
            'username'          => 'user_1',
            'email'             => 'user_1@example.com',
            'firstName'         => 'Test1',
            'lastName'          => 'Test1',
            'plainPassword'     => self::PASSWORD,
            'apiKey'            => 'user_1_api_key',
            'isAdministrator'   => true
        ],
        self::USER_2 => [
            'username'          => 'user_2',
            'email'             => 'user_2@example.com',
            'firstName'         => 'Test2',
            'lastName'          => 'Test2',
            'plainPassword'     => self::PASSWORD,
            'apiKey'            => 'user_2_api_key',
        ],
    ];

    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('oro_user.manager');
        /** @var Organization $organization */
        $organization = $this->getReference('organization');
        /** @var BusinessUnit $businessUnit */
        $businessUnit = $this->getReference('business_unit');
        $defaultRole = $manager->getRepository(Role::class)->findOneBy(['role' => LoadPOSRolesData::ROLE_USER]);

        foreach ($this->data as $reference => $data) {
            /** @var User $user */
            $user = $userManager->createUser();
            $user->setOwner($businessUnit);
            $user->setOrganization($organization);
            $user->addOrganization($organization);

            $role = $defaultRole;
            if (!empty($data['isAdministrator'])) {
                $role = $manager->getRepository(Role::class)->findOneBy(['role' => LoadPOSRolesData::ROLE_ADMIN]);
            }
            unset($data['isAdministrator']);
            $user->addUserRole($role);
            $this->setEntityPropertyValues(
                $user,
                $data,
                ['apiKey']
            );

            if (isset($data['apiKey'])) {
                $apiKey = new UserApi();
                $apiKey->setApiKey($data['apiKey']);
                $apiKey->setOrganization($organization);
                $manager->persist($apiKey);
                $user->addApiKey($apiKey);
            }

            $userManager->updateUser($user);
            $this->setReference($reference, $user);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [LoadOrganization::class, LoadBusinessUnit::class];
    }
}
