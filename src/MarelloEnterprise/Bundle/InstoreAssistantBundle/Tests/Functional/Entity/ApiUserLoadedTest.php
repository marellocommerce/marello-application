<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Tests\Functional\Entity;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Migrations\Data\ORM\LoadApiUser;

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

        $this->assertTrue((bool)$user->getRole(self::ROLE_CUSTOM_API_USER), 'User has the custom assigned role');
        $this->assertTrue($user->isEnabled(), 'User is ready and enabled');
        $this->assertNotNull($user, 'Api User is loaded Correctly');
        $this->assertNotNull($user->getApiKeys(), 'Api User has a generated API key');
    }
}
