<?php

namespace Marello\Bundle\ProductBundle\Tests\Functional\Controller;

use Marello\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\UIBundle\Route\Router;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 */
class ProductAttributeFamilyControllerTest extends WebTestCase
{
    const PRODUCT_ENTITY_ALIAS = 'marelloproduct';

    public function setUp(): void
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );
    }

    /**
     * just checking wether we are getting a 200 result back and not an error when navigating to this route
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
     * Test product attribute family creation and saving
     */
    public function testProductAttributeFamilyCreate()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_attribute_family_create', ['alias' => self::PRODUCT_ENTITY_ALIAS])
        );

        $attributeFamilyCode = 'random_attribute_family_code';
        $attributeFamilyLabel = 'Random Attribute Family Label';
        $saveButton    = $crawler->selectButton('Save and Close');

        $form = $saveButton->form();
        $form['oro_attribute_family[code]'] = $attributeFamilyCode;
        $form['oro_attribute_family[labels][values][default]'] = $attributeFamilyLabel;
        $form['oro_attribute_family[isEnabled]'] = true;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form, [Router::ACTION_PARAMETER => $saveButton->attr('data-action')]);
        $result  = $this->client->getResponse();

        $this->assertHtmlResponseStatusCodeEquals($result, Response::HTTP_OK);
        $this->assertStringContainsString('Product Family was successfully saved', $crawler->html());
        $this->assertStringContainsString($attributeFamilyCode, $crawler->html());
        $this->assertStringContainsString($attributeFamilyLabel, $crawler->html());

        return $attributeFamilyCode;
    }

    /**
     * Test product attribute family creation and saving
     * @param $attributeFamilyCode string
     * @depends testProductAttributeFamilyCreate
     */
    public function testProductAttributeFamilyUpdate($attributeFamilyCode)
    {
        $response = $this->client->requestGrid(
            'attribute-family-grid',
            [
                'attribute-family-grid[entity_class]' => Product::class,
                'attribute-family-grid[_filter][code][value]' => $attributeFamilyCode
            ]
        );

        $result = $this->getJsonResponseContent($response, Response::HTTP_OK);
        $result = reset($result['data']);
        $crawler     = $this->client->request(
            'GET',
            $this->getUrl('oro_attribute_family_update', ['id' => $result['id']])
        );

        $saveButton = $crawler->selectButton('Save and Close');

        $form = $saveButton->form();
        $form['oro_attribute_family[code]'] = 'newAttributeCode';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertStringContainsString('Successfully updated', $crawler->html());
        $this->assertStringContainsString('newAttributeCode', $crawler->html());
    }
}
