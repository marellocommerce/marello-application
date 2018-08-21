<?php

namespace Marello\Bundle\SalesBundle\Tests\Functional\Controller;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelType;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class SalesChannelControllerTest extends WebTestCase
{
    const NAME = 'name';
    const CODE = 'code';
    const CHANNEL_TYPE = 'channelType';
    const CURRENCY = 'USD';
    const DEFAULT = true;
    const ACTIVE = true;
    const LOCALIZATION = 1;
    const LOCALE = 'nl_NL';

    const UPDATED_NAME = 'updatedName';
    const UPDATED_CODE = 'updatedCode';
    const UPDATED_CHANNEL_TYPE = 'updatedChannelType';
    const UPDATED_CURRENCY = 'USD';
    const UPDATED_DEFAULT = false;
    const UPDATED_ACTIVE = false;
    const UPDATED_LOCALIZATION = 1;
    const UPDATED_LOCALE = 'nl_NL';

    const SAVE_MESSAGE = 'Sales Channel has been saved successfully';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );

        $this->loadFixtures([
            LoadSalesData::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function testIndex()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_sales_saleschannel_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertContains('marello-sales-channel', $crawler->html());
    }

    /**
     * {@inheritdoc}
     * @return int
     */
    public function testCreate()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_sales_saleschannel_create')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertSalesChannelSave(
            $crawler,
            self::NAME,
            self::CODE,
            self::CHANNEL_TYPE,
            self::CURRENCY,
            self::DEFAULT,
            self::ACTIVE,
            self::LOCALIZATION,
            self::LOCALE
        );

        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloSalesBundle:SalesChannel')
            ->getRepository('MarelloSalesBundle:SalesChannel')
            ->findOneBy(['code' => self::CODE]);
        $this->assertNotEmpty($salesChannel);

        return $salesChannel->getId();
    }

    /**
     * {@inheritdoc}
     * @param int $id
     * @return int
     * @depends testCreate
     */
    public function testUpdate($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_sales_saleschannel_update', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertSalesChannelSave(
            $crawler,
            self::UPDATED_NAME,
            self::UPDATED_CODE,
            self::UPDATED_CHANNEL_TYPE,
            self::UPDATED_CURRENCY,
            self::UPDATED_DEFAULT,
            self::UPDATED_ACTIVE,
            self::UPDATED_LOCALIZATION,
            self::UPDATED_LOCALE
        );

        return $id;
    }

    /**
     * {@inheritdoc}
     * @depends testUpdate
     * @param int $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_sales_saleschannel_view', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertViewPage(
            $crawler->html(),
            self::UPDATED_NAME,
            self::UPDATED_CODE,
            self::UPDATED_CHANNEL_TYPE,
            self::UPDATED_CURRENCY,
            self::UPDATED_DEFAULT,
            self::UPDATED_ACTIVE,
            self::UPDATED_LOCALE
        );
    }
    
    /**
     * {@inheritdoc}
     * @depends testUpdate
     * @param int $id
     */
    public function testDelete($id)
    {
        $operationName = 'DELETE';
        $this->client->request(
            'POST',
            $this->getUrl(
                'oro_action_operation_execute',
                [
                    'operationName' => $operationName,
                    'entityClass'   => SalesChannel::class,
                    'entityId'      => $id,
                ]
            ),
            $this->getOperationExecuteParams($operationName, $id, SalesChannel::class),
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);
        $this->assertEquals(
            [
                'success'     => true,
                'message'     => '',
                'messages'    => [],
                'redirectUrl' => $this->getUrl('marello_sales_saleschannel_index'),
                'pageReload' => true
            ],
            json_decode($this->client->getResponse()->getContent(), true)
        );

        $this->client->request('GET', $this->getUrl('marello_sales_saleschannel_view', ['id' => $id]));

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }

    /**
     * {@inheritdoc}
     * @param Crawler $crawler
     * @param string $name
     * @param string $code
     * @param string $channelType
     * @param string $currency
     * @param bool $default
     * @param bool $active
     * @param int $localization
     * @param string $locale
     */
    protected function assertSalesChannelSave(
        Crawler $crawler,
        $name,
        $code,
        $channelType,
        $currency,
        $default,
        $active,
        $localization,
        $locale
    ) {
        $token = $this->getContainer()->get('security.csrf.token_manager')
            ->getToken(SalesChannelType::NAME)->getValue();

        $formData = [
            'input_action' => '{"route":"marello_sales_saleschannel_view","params":{"id":"$id"}}',
            SalesChannelType::NAME => [
                'name' => $name,
                'code' => $code,
                'channelType' => $channelType,
                'currency' => $currency,
                'default' => $default,
                'active' => $active,
                'localization' => $localization,
                'locale' => $locale,
                '_token' => $token,
            ],
        ];

        $form = $crawler->selectButton('Save and Close')->form();

        $this->client->followRedirects(true);
        $crawler = $this->client->request($form->getMethod(), $form->getUri(), $formData);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $html = $crawler->html();

        $this->assertContains(self::SAVE_MESSAGE, $html);
        $this->assertViewPage($html, $name, $code, $channelType, $currency, $default, $active, $locale);
    }
    
    /**
     * {@inheritdoc}
     * @param string $html
     * @param string $name
     * @param string $code
     * @param string $channelType
     * @param string $currency
     * @param bool $default
     * @param bool $active
     * @param string $locale
     */
    protected function assertViewPage(
        $html,
        $name,
        $code,
        $channelType,
        $currency,
        $default,
        $active,
        $locale
    ) {
        $this->assertContains($name, $html);
        $this->assertContains($code, $html);
        $this->assertContains($channelType, $html);
        $this->assertContains($currency, $html);
        $this->assertContains($default ? 'Yes' : 'No', $html);
        $this->assertContains($active ? 'Yes' : 'No', $html);
        $this->assertContains($locale, $html);
    }

    /**
     * {@inheritdoc}
     * @param $operationName
     * @param $entityId
     * @param $entityClass
     *
     * @return array
     */
    protected function getOperationExecuteParams($operationName, $entityId, $entityClass)
    {
        $actionContext = [
            'entityId'    => $entityId,
            'entityClass' => $entityClass
        ];
        $container = self::getContainer();
        $operation = $container->get('oro_action.operation_registry')->findByName($operationName);
        $actionData = $container->get('oro_action.helper.context')->getActionData($actionContext);

        $tokenData = $container->get('oro_action.operation.execution.form_provider')
            ->createTokenData($operation, $actionData);
        $container->get('session')->save();

        return $tokenData;
    }
}
