<?php

namespace MarelloEnterprise\Bundle\InstoreAssistantBundle\Tests\Functional\Controller;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;

use MarelloEnterprise\Bundle\InstoreAssistantBundle\Api\Model\InstoreUserApi;
use MarelloEnterprise\Bundle\InstoreAssistantBundle\Migrations\Data\ORM\LoadApiUser;

class InstoreUserControllerTest extends RestJsonApiTestCase
{
    /**
     * setup with WSSE header generated from the InstoreAssistantUser instead
     * of the 'plain' admin user to simulate a more accurate test env
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
    public function testUserIsAuthenticatedWithUsernameOrEmail()
    {
        $userToBeLoggedIn = $this->getUser('admin@example.com');
        /** @var UserApi $apiKey */
        $apiKey =  $userToBeLoggedIn->getApiKeys()->first();
        $request = array(
            "instoreuser" => array(
                "username" => $userToBeLoggedIn->getUsername(),
                "credentials" => $userToBeLoggedIn->getPlainPassword()
            )
        );

        $response = $this->request(
            'POST',
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
