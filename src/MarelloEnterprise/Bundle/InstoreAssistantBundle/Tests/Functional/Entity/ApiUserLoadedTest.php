<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Tests\Functional\Entity;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Migrations\Data\ORM\LoadApiUser;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Migrations\Data\ORM\LoadApiUserRole;

class ApiUserLoadedTest extends WebTestCase
{
    use EntityTestCaseTrait;

    const ROLE_CUSTOM_API_USER = 'ROLE_CUSTOM_API_USER';

    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );
    }

    /**
     * test if the Api user is loaded correctly
     */
    public function testIsApiUserLoadedAndHasApiKey()
    {
        /** @var UserManager $userManager */
        $userManager = $this->getContainer()->get('oro_user.manager');
        /** @var User $user */
        $user = $userManager->loadUserByUsername(LoadApiUser::INSTORE_API_USERNAME);

        $this->assertTrue(
            (bool)$user->getRole(LoadApiUserRole::ROLE_INSTORE_ASSISTANT_API_USER),
            'User has the custom assigned role'
        );
        $this->assertTrue($user->isEnabled(), 'User is ready and enabled');
        $this->assertNotNull($user, 'Api User is loaded Correctly');
        $this->assertNotEmpty($user->getApiKeys(), 'Api User has a generated API key');
    }

    /**
     * test if API user only has access to view other users and no additional permissions
     */
    public function testApiUserOnlyHasAccessToViewUsers()
    {
        /** @var UserManager $userManager */
        $userManager = $this->getContainer()->get('oro_user.manager');
        /** @var User $user */
        $user = $userManager->loadUserByUsername(LoadApiUser::INSTORE_API_USERNAME);
        // using the Oro Security Facade instead of directly using the SecurityContext during the deprecation
        // since Symfony version 2.6 and removal in Symfony 3.0
        $securityContext = $this->getContainer()->get('oro_security.security_facade');
        if (!$securityContext->getToken()) {
            $this->updateUserSecurityToken($user->getEmail());
        }
        $this->assertTrue(
            $securityContext->isGranted('oro_user_user_view', $user),
            'User CAN view other users'
        );

        $this->assertFalse(
            $securityContext->isGranted('oro_user_user_create', $user),
            'User CANNOT create other users'
        );

        $this->assertFalse(
            $securityContext->isGranted('oro_user_user_update', $user),
            'User CANNOT update other users'
        );
    }
}
