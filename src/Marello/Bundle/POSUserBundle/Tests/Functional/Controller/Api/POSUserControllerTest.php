<?php

namespace Marello\Bundle\POSUserBundle\Tests\Functional\Controller\Api;

use Marello\Bundle\POSUserBundle\Migrations\Data\ORM\LoadPOSRolesData;
use Marello\Bundle\POSUserBundle\Tests\Functional\DataFixtures\LoadUserData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;

class POSUserControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures([
            LoadUserData::class
        ]);
    }

    public function testAuthAdminByUsername()
    {
        /** @var User $user */
        $user = $this->getReference(LoadUserData::USER_1);

        $this->client->jsonRequest(
            'PUT',
            $this->getUrl('marello_posuser_rest_api_authenticate_user'),
            [
                'username' => $user->getUsername(),
                'credentials' => LoadUserData::PASSWORD,
            ]
        );
        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('apiKey', $responseData);
        self::assertEquals('user_1_api_key', $responseData['apiKey']);
        self::assertArrayHasKey('roles', $responseData);
        self::assertEquals([LoadPOSRolesData::ROLE_ADMIN], $responseData['roles']);
    }

    public function testAuthUserByEmail()
    {
        /** @var User $user */
        $user = $this->getReference(LoadUserData::USER_2);

        $this->client->jsonRequest(
            'PUT',
            $this->getUrl('marello_posuser_rest_api_authenticate_user'),
            [
                'email' => $user->getEmail(),
                'credentials' => LoadUserData::PASSWORD,
            ]
        );
        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('apiKey', $responseData);
        self::assertEquals('user_2_api_key', $responseData['apiKey']);
        self::assertArrayHasKey('roles', $responseData);
        self::assertEquals([LoadPOSRolesData::ROLE_USER], $responseData['roles']);
    }

    public function testFailedAuth()
    {
        $this->client->jsonRequest(
            'PUT',
            $this->getUrl('marello_posuser_rest_api_authenticate_user'),
            [
                'email' => 'some@random.email',
                'credentials' => LoadUserData::PASSWORD,
            ]
        );
        $response = $this->client->getResponse();

        self::assertEmpty($response->getContent());
    }
}
