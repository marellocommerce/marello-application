<?php

namespace Marello\Bundle\CatalogBundle\Tests\Functional\Controller;

use Symfony\Component\DomCrawler\Form;
    use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\CatalogBundle\Tests\Functional\DataFixtures\LoadCategoryData;

/**
 * @outputBuffering enabled
 */
class CategoryControllerTest extends WebTestCase
{
    const GRID_NAME = 'marello-categories-grid';

    public function setUp()
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );

        $this->loadFixtures([
            LoadCategoryData::class
        ]);
    }

    /**
     * Test getting grid without errors
     */
    public function testCategoryIndex()
    {
        $this->client->request('GET', $this->getUrl('marello_category_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        $response = $this->client->requestGrid(self::GRID_NAME);
        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $this->assertCount(3, $result);
    }
}
