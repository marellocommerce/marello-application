<?php

namespace Marello\Bundle\CoreBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase as BaseApiTestCase;

class RestJsonApiTestCase extends BaseApiTestCase
{
    /**
     * Assert that the response is of type 'application/vnd.api+json'
     * @param Response $response
     */
    public static function assertJsonResponse(Response $response)
    {
        self::assertResponseContentTypeEquals($response, self::JSON_API_CONTENT_TYPE);
    }
}
