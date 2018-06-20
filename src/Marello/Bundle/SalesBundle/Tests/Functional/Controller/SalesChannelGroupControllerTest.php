<?php

namespace Marello\Bundle\SalesBundle\Tests\Functional\Controller;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Marello\Bundle\SalesBundle\Form\Type\SalesChannelGroupType;
use Marello\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadSalesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class SalesChannelGroupControllerTest extends WebTestCase
{
    const NAME = 'name';
    const DESCRIPTION = 'description';

    const UPDATED_NAME = 'updatedName';
    const UPDATED_DESCRIPTION = '';

    const SAVE_MESSAGE = 'Sales Channel Group has been saved successfully';

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
            $this->getUrl('marello_sales_saleschannelgroup_index')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
        $this->assertContains('marello-sales-channel-groups', $crawler->html());
    }

    /**
     * {@inheritdoc}
     * @return int
     */
    public function testCreate()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('marello_sales_saleschannelgroup_create')
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertSalesChannelGroupSave(
            $crawler,
            self::NAME,
            self::DESCRIPTION,
            [$this->getReference(LoadSalesData::CHANNEL_1_REF), $this->getReference(LoadSalesData::CHANNEL_2_REF)]
        );

        /** @var SalesChannelGroup $salesChannelGroup */
        $salesChannelGroup = $this->getContainer()->get('doctrine')
            ->getManagerForClass('MarelloSalesBundle:SalesChannelGroup')
            ->getRepository('MarelloSalesBundle:SalesChannelGroup')
            ->findOneBy(['name' => self::NAME]);
        $this->assertNotEmpty($salesChannelGroup);

        return $salesChannelGroup->getId();
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
            $this->getUrl('marello_sales_saleschannelgroup_update', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $this->assertSalesChannelGroupSave(
            $crawler,
            self::UPDATED_NAME,
            self::UPDATED_DESCRIPTION,
            [$this->getReference(LoadSalesData::CHANNEL_2_REF), $this->getReference(LoadSalesData::CHANNEL_3_REF)]
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
            $this->getUrl('marello_sales_saleschannelgroup_view', ['id' => $id])
        );

        $this->assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);

        $channelNames = array_map(
            function (SalesChannel $channel) {
                return $channel->getName();
            },
            [$this->getReference(LoadSalesData::CHANNEL_2_REF), $this->getReference(LoadSalesData::CHANNEL_3_REF)]
        );

        $this->assertViewPage($crawler->html(), self::UPDATED_NAME, self::UPDATED_DESCRIPTION, $channelNames);
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
                    'entityClass'   => SalesChannelGroup::class,
                    'entityId'      => $id,
                ]
            ),
            $this->getOperationExecuteParams($operationName, $id, SalesChannelGroup::class),
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);
        $this->assertEquals(
            [
                'success'     => true,
                'message'     => '',
                'messages'    => [],
                'redirectUrl' => $this->getUrl('marello_sales_saleschannelgroup_index'),
                'pageReload' => true
            ],
            json_decode($this->client->getResponse()->getContent(), true)
        );

        $this->client->request('GET', $this->getUrl('marello_sales_saleschannelgroup_view', ['id' => $id]));

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }

    /**
     * {@inheritdoc}
     * @param Crawler $crawler
     * @param string $name
     * @param string $description
     * @param SalesChannel[] $channels
     */
    protected function assertSalesChannelGroupSave(Crawler $crawler, $name, $description, array $channels)
    {
        $token = $this->getContainer()->get('security.csrf.token_manager')
            ->getToken(SalesChannelGroupType::NAME)->getValue();

        $channelIds = array_map(
            function (SalesChannel $channel) {
                return $channel->getId();
            },
            $channels
        );

        $channelNames = array_map(
            function (SalesChannel $channel) {
                return $channel->getName();
            },
            $channels
        );

        $formData = [
            'input_action' => '{"route":"marello_sales_saleschannelgroup_view","params":{"id":"$id"}}',
            SalesChannelGroupType::NAME => [
                'name' => $name,
                'description' => $description,
                'salesChannels' => implode(',', $channelIds),
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
        $this->assertViewPage($html, $name, $description, $channelNames);
    }
    
    /**
     * {@inheritdoc}
     * @param string $html
     * @param string $name
     * @param string $description
     * @param array $channelNames
     */
    protected function assertViewPage($html, $name, $description, array $channelNames)
    {
        $this->assertContains($name, $html);
        $this->assertContains($description ? : 'N/A', $html);
        $this->assertContains('marello-group-sales-channels', $html);
        foreach ($channelNames as $name) {
            $this->assertContains($name, $html);
        }
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
