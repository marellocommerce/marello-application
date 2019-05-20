<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\EntityConfigBundle\Tests\Functional\Controller\AttributeControllerTest;

/**
 * @outputBuffering enabled
 */
class ProductAttributeControllerTest extends AttributeControllerTest
{
    const PRODUCT_ENTITY_ALIAS = 'marelloproduct';

    /**
     * just checking whether we are getting a 200 result back and not an error when navigating to this route
     */
    public function testProductAttributeIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_attribute_index', ['alias' => self::PRODUCT_ENTITY_ALIAS]));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
    }

    /**
     * @return string
     */
    protected function getTestEntityAlias()
    {
        return self::PRODUCT_ENTITY_ALIAS;
    }

    /**
     * {@inheritdoc}
     */
    public function testCreateImage()
    {
        // skip test during hardcoded entity classname in dependent functions
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function testCreateFile()
    {
        // skip test during hardcoded entity classname in dependent functions
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
