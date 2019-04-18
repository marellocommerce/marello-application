<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 */
class ProductAttributeControllerTest extends WebTestCase
{
    const PRODUCT_ENTITY_ALIAS = 'marelloproduct';

    public function setUp()
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );
    }

    /**
     * just checking wether we are getting a 200 result back and not an error when navigating to this route
     * {@inheritdoc}
     */
    public function testProductAttributeFamilyIndex()
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_attribute_family_index', ['alias' => self::PRODUCT_ENTITY_ALIAS])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    /**
     * just checking wether we are getting a 200 result back and not an error when navigating to this route
     * {@inheritdoc}
     */
    public function testProductAttributeIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_attribute_index', ['alias' => self::PRODUCT_ENTITY_ALIAS]));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
    }
}
