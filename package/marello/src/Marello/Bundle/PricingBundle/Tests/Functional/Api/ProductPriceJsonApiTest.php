<?php

namespace Marello\Bundle\PricingBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Marello\Bundle\PricingBundle\Model\PriceTypeInterface;
use Marello\Bundle\CoreBundle\Tests\Functional\RestJsonApiTestCase;
use Marello\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;

class ProductPriceJsonApiTest extends RestJsonApiTestCase
{
    const TESTING_ENTITY = 'marelloproductprices';

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadProductData::class
        ]);
    }

    /**
     * Test cget (getting a list of products) of Product entity
     */
    public function testGetListOfPrices()
    {
        $response = $this->cget(['entity' => self::TESTING_ENTITY], []);

        $this->assertJsonResponse($response);
        $this->assertResponseStatusCodeEquals($response, Response::HTTP_OK);
        $this->assertResponseCount(8, $response);
        $this->assertResponseContains('cget_prices.yml', $response);
    }

    /**
     * Test get prices filtered by PriceType 'default'
     */
    public function testGetPricesByDefaultPriceType()
    {
        $response = $this->cget(
            ['entity' => self::TESTING_ENTITY],
            [
                'filter' => ['pricetype' =>  PriceTypeInterface::DEFAULT_PRICE ]
            ]
        );
        $this->assertJsonResponse($response);
        $this->assertResponseContains('get_prices_by_price_type_default.yml', $response);
    }
}
