<?php

namespace Marello\Bundle\HealthCheckBundle\Tests\Functional\Controller;

use Oro\Bundle\IntegrationBundle\Tests\Functional\DataFixtures\LoadStatusData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class IntegrationStatusControllerTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadStatusData::class,
        ]);
    }

    public function testIndexAction()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'marello_healthcheck_integration_statuses_index',
                []
            )
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }
}
