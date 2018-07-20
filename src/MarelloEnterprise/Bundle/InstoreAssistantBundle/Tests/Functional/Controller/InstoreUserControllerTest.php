<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Tests\Functional\Controller;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\ApiBundle\Request\Constraint;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Migrations\Data\ORM\LoadApiUser;
use Oro\Bundle\UserProBundle\Security\LoginAttemptsProvider;

class InstoreUserControllerTest extends RestJsonApiTestCase
{
    /**
     * setup with WSSE header generated from the InstoreAssistantUser instead
     * of the 'plain' admin user to create a more accurate test env
     */
    protected function setUp()
    {
        // setup the original first, than replace the initalized client
        parent::setUp();

        $this->initClient(
            [],
            array_replace(
                $this->generateWsseAuthHeader(LoadApiUser::INSTORE_API_USERNAME, LoadApiUser::INSTORE_API_USERNAME),
                ['CONTENT_TYPE' => self::JSON_API_CONTENT_TYPE]
            )
        );
    }

    /**
     * Test whether a user is succesfully authenticated when providing correct credentials
     */
    public function testUserIsAuthenticatedWithEmail()
    {
        $userToBeLoggedIn = $this->getUser('admin@example.com');
        /** @var UserApi $apiKey */
        $apiKey =  $userToBeLoggedIn->getApiKeys()->first();
        $entityType = $this->getEntityType(InstoreUserApi::class);
        $request = [
            'data' => [
                'type' => $entityType,
                'attributes' => [
                    'email' => self::AUTH_USER,
                    'credentials' => self::AUTH_PW
                ]
            ]
        ];

        $response = $this->request(
            'PUT',
            $this->getUrl('marelloenterprise_instoreassistant_rest_api_authenticate_instore_user'),
            $request
        );

        self::assertResponseStatusCodeEquals($response, 201);
        self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);
        $result = self::jsonToArray($response->getContent());
        self::assertEquals(
            $this->getEntityType(InstoreUserApi::class),
            $result['data']['type']
        );

        self::assertEquals(
            $apiKey->getId(),
            $result['data']['id']
        );
        self::assertEquals(
            $apiKey->getApiKey(),
            $result['data']['attributes']['apiKey']
        );
    }

    /**
     * Test whether a user is succesfully authenticated when providing correct credentials
     */
    public function testUserIsAuthenticatedWithUsername()
    {
        $userToBeLoggedIn = $this->getUser('admin@example.com');
        /** @var UserApi $apiKey */
        $apiKey =  $userToBeLoggedIn->getApiKeys()->first();
        $entityType = $this->getEntityType(InstoreUserApi::class);
        $request = [
            'data' => [
                'type' => $entityType,
                'attributes' => [
                    'username' => self::USER_NAME,
                    'credentials' => self::AUTH_PW
                ]
            ]
        ];

        $response = $this->request(
            'PUT',
            $this->getUrl('marelloenterprise_instoreassistant_rest_api_authenticate_instore_user'),
            $request
        );

        self::assertResponseStatusCodeEquals($response, 201);
        self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);
        $result = self::jsonToArray($response->getContent());
        self::assertEquals(
            $this->getEntityType(InstoreUserApi::class),
            $result['data']['type']
        );

        self::assertEquals(
            $apiKey->getId(),
            $result['data']['id']
        );
        self::assertEquals(
            $apiKey->getApiKey(),
            $result['data']['attributes']['apiKey']
        );
    }

    /**
     * Test whether a user is succesfully authenticated when providing correct credentials
     */
    public function testUsernamePasswordCombinationIsNotValid()
    {
        $userToBeLoggedIn = $this->getUser('admin@example.com');

        $entityType = $this->getEntityType(InstoreUserApi::class);
        $request = [
            'data' => [
                'type' => $entityType,
                'attributes' => [
                    'username' => $userToBeLoggedIn->getUsername(),
                    'credentials' => 'not a valid password'
                ]
            ]
        ];

        /** @var LoginAttemptsProvider $loginAttemptProvider */
        $loginAttemptProvider = $this->getContainer()->get('oro_userpro.security.login_attempts_provider');
        // make sure that the remaining attempt is equal to the limit when no-one actually loggedin the user
        self::assertEquals(
            $loginAttemptProvider->getRemaining($userToBeLoggedIn),
            $loginAttemptProvider->getLimit()
        );

        $response = $this->request(
            'PUT',
            $this->getUrl('marelloenterprise_instoreassistant_rest_api_authenticate_instore_user'),
            $request
        );

        self::assertResponseStatusCodeEquals($response, 400);
        self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);
        $result = self::jsonToArray($response->getContent());
        self::assertArrayHasKey('errors', $result);

        foreach ($result['errors'] as $error) {
            self::assertEquals(
                "400",
                $error['status']
            );

            self::assertEquals(
                Constraint::REQUEST_DATA,
                $error['title']
            );

            self::assertEquals(
                "Could not validate user with specified username and credentials",
                $error['detail']
            );
        }
    }

    /**
     * Get User by email address
     * @param string $email
     * @param string $userClass
     * @return User
     */
    private function getUser($email, $userClass = User::class)
    {
        return $this->getContainer()->get('doctrine')->getRepository($userClass)->findOneBy(['email' => $email]);
    }
}
