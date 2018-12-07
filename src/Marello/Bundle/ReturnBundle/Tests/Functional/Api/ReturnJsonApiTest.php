<?php

namespace Marello\Bundle\ReturnBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\ReturnBundle\Entity\ReturnEntity;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\ReturnBundle\Tests\Functional\DataFixtures\LoadReturnData;
use Marello\Bundle\ReturnBundle\Tests\Functional\DataFixtures\LoadReturnWorkflowData;

class ReturnJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marelloreturns';

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadReturnData::class,
            LoadReturnWorkflowData::class
        ]);
    }

    /**
     * Test cget (getting a list of returns) of Return entity
     *
     */
    public function testGetListOfReturns()
    {
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseCount(9, $response);
        $this->assertResponseContains('cget_return_list.yml', $response);
    }

    /**
     * Test get return by id
     */
    public function testGetReturnById()
    {
        /** @var ReturnEntity $return */
        $return = $this->getReference('return1');
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => $return->getId()],
            []
        );

        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_return_by_id.yml', $response);
    }

    /**
     * Create a new Return
     */
    public function testCreateNewReturn()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'return_create.yml'
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());
        /** @var ReturnEntity $return */
        $return = $this->getEntityManager()->find(ReturnEntity::class, $responseContent->data->id);
        $this->assertCount(
            $return->getReturnItems()->count(),
            $responseContent->data->relationships->returnItems->data
        );
    }

    /**
     * Test get not found
     */
    public function testGetNotFound()
    {
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => 1000],
            [],
            [],
            false
        );

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
