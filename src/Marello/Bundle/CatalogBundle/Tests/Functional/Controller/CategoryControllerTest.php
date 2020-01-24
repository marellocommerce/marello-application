<?php

namespace Marello\Bundle\CatalogBundle\Tests\Functional\Controller;

use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use Marello\Bundle\CatalogBundle\Tests\Functional\DataFixtures\LoadCategoryData;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

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

    /**
     * {@inheritdoc}
     */
    public function testCategoryCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('marello_category_create'));
        $name    = 'Category 1';
        $code     = 'category1';
        $form    = $crawler->selectButton('Save and Close')->form();
        $product = $this->getReference(LoadProductData::PRODUCT_1_REF);

        $form['marello_catalog_category[name]']               = $name;
        $form['marello_catalog_category[code]']                = $code;
        $form['marello_catalog_category[appendProducts]'] = $product->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result  = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains('Category has been saved', $crawler->html());

        $response = $this->client->requestGrid(self::GRID_NAME);
        self::assertJsonResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertContains($name, $response->getContent());

        return $name;
    }

    /**
     * @param string $name
     *
     * @depends testCategoryCreate
     *
     * @return string
     */
    public function testUpdateCategory($name)
    {
        $response = $this->client->requestGrid(
            self::GRID_NAME,
            [self::GRID_NAME .'[_filter][name][value]' => $name]
        );

        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $result = reset($result['data']);

        $resultData = $result;
        $crawler     = $this->client->request(
            'GET',
            $this->getUrl('marello_category_update', ['id' => $result['id']])
        );
        $result      = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);

        /** @var Form $form */
        $form                                   = $crawler->selectButton('Save and Close')->form();
        $name                                   = 'name' . $this->generateRandomString();
        $form['marello_catalog_category[name]'] = $name;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains("Category has been saved", $crawler->html());

        $resultData['name'] = $name;

        return $resultData;
    }

    /**
     * @param array $resultData
     *
     * @depends testUpdateCategory
     */
    public function testCategoryView($resultData)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_category_view', ['id' => $resultData['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertContains("{$resultData['name']}", $crawler->html());
    }
}
