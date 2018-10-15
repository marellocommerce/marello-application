<?php

namespace Marello\Bundle\CatalogBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\CatalogBundle\Entity\Category;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\CatalogBundle\Tests\Functional\DataFixtures\LoadCategoryData;

class CategoryJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'categories';

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadCategoryData::class
        ]);
    }

    /**
     * Test cget (getting a list of categories) of Category entity
     */
    public function testGetListOfCategories()
    {
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);


        $this->assertResponseCount(3, $response);
        $this->assertResponseContains('cget_categories_list.yml', $response);
    }

    /**
     * Test get product by id
     */
    public function testGetCategoryById()
    {
        /** @var Category $product */
        $category = $this->getReference(LoadCategoryData::CATEGORY_1_REF);
        $response = $this->get(
            ['entity' => self::TESTING_ENTITY, 'id' => $category->getId()],
            []
        );

        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_category_by_id.yml', $response);
    }

    /**
     * Test Create new Product
     */
    public function testCreateNewCategory()
    {
        $response = $this->post(
            ['entity' => self::TESTING_ENTITY],
            'category_create.yml'
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());

        /** @var Category $category */
        $category = $this->getEntityManager()->find(Category::class, $responseContent->data->id);
        $this->assertEquals($category->getName(), $responseContent->data->attributes->name);
    }

    /**
     * Test update Category
     */
    public function testUpdateCategory()
    {
        /** @var Category $existingCategory */
        $existingCategory = $this->getReference(LoadCategoryData::CATEGORY_1_REF);
        $existingCategoryName = $existingCategory->getName();
        $response = $this->patch(
            [
                'entity' => self::TESTING_ENTITY,
                'id' => $existingCategory->getId()
            ],
            'category_update.yml'
        );

        $this->assertJsonResponse($response);
        $responseContent = json_decode($response->getContent());

        /** @var Category $category */
        $category = $this->getEntityManager()->find(Category::class, $responseContent->data->id);
        $this->assertEquals($category->getName(), $responseContent->data->attributes->name);
        $this->assertNotEquals($category->getName(), $existingCategoryName);
    }
}
