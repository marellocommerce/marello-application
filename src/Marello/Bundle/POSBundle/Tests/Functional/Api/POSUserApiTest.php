<?php

namespace Marello\Bundle\POSBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\POSBundle\Tests\Functional\DataFixtures\LoadUserData;

class POSUserApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marelloposusers';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadUserData::class
        ]);
    }

    /**
     * Test user login via API
     */
    public function testLoginUser()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'pos_userapi_login.yml',
            [],
            false
        );

        $userReference = $this->getReference('test_user_1');
        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $responseContent = json_decode($response->getContent());
        $this->assertEquals($userReference->getApiKeys()->first()->getApiKey(), $responseContent->meta->apiKey);
        $this->assertNotEmpty($responseContent->meta->roles);
    }

    /**
     * Test user login via API
     */
    public function testFailLoginUser()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'pos_userapi_login_fail.yml',
            [],
            false
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals('The user authentication fails. Reason: Invalid username or password.', $responseContent->errors[0]->detail);
        $this->assertEquals($responseContent->errors[0]->status, Response::HTTP_FORBIDDEN);
    }
}
