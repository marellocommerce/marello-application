<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Tests\Functional\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Migrations\Data\ORM\LoadApiUser;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Provider\InstoreUserAuthenticationProvider;

class InstoreUserAuthenticationProviderTest extends WebTestCase
{
    /** @var InstoreUserAuthenticationProvider $authProvider */
    protected $authProvider;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader()
        );

        $this->authProvider = $this->getContainer()->get('marelloenterprise_instoreassistant.provider.auth_provider');
    }

    /**
     * {@inheritdoc}
     */
    public function testNonExistingUser()
    {
        $username = 'fakeuser';
        $password = LoadApiUser::INSTORE_API_USERNAME;

        $this->expectException(UsernameNotFoundException::class);
        $this->expectExceptionMessage(sprintf('No user with name "%s" was found.', $username));
        $this->authProvider->authenticateInstoreUser($username, $password);
    }

    /**
     * @dataProvider getUserData
     * @param array $credentials
     * @param $result
     */
    public function testUserScenario(array $credentials, $result)
    {
        $username = $credentials['username'];
        $password = $credentials['password'];

        static::assertEquals($result, $this->authProvider->authenticateInstoreUser($username, $password));
    }

    /**
     * user data provider
     */
    public function getUserData()
    {
        return [
            [
                'credentials' => [
                    'username' => LoadApiUser::INSTORE_API_USERNAME,
                    'password' => LoadApiUser::INSTORE_API_USERNAME
                ],
                'result' => true,
                'message' => 'User has entered correct credentials'
            ],
            [
                'credentials' => [
                    'username' => LoadApiUser::INSTORE_API_USERNAME,
                    'password' => $this->generateRandomString()
                ],
                'result' => false,
                'message' => 'User has entered incorrect credentials'
            ]
        ];
    }
}
