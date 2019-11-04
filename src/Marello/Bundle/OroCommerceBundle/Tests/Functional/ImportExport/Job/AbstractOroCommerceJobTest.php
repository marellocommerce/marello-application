<?php

namespace Marello\Bundle\OroCommerceBundle\Tests\Functional\ImportExport\Job;

use Doctrine\Common\Persistence\ManagerRegistry;
use Marello\Bundle\OroCommerceBundle\Client\Factory\OroCommerceRestClientFactory;
use Marello\Bundle\OroCommerceBundle\Client\OroCommerceRestClient;
use Marello\Bundle\OroCommerceBundle\Integration\Transport\Rest\OroCommerceRestTransport;
use Marello\Bundle\OroCommerceBundle\Tests\Functional\DataFixtures\LoadChannelData;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessor;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

abstract class AbstractOroCommerceJobTest extends WebTestCase
{
    const SYNC_PROCESSOR = 'oro_integration.sync.processor';
    const REVERSE_SYNC_PROCESSOR = 'oro_integration.reverse_sync.processor';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $restClient;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    protected $restClientFactory;

    /**
     * @var OroCommerceRestClientFactory
     */
    protected $realRestClientFactory;

    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient();

        $this->stubResources();
        $this->managerRegistry = $this->getContainer()->get('doctrine');
        $this->loadFixtures([LoadChannelData::class]);
        $this->channel = $this->getReference('orocommerce_channel:first_test_channel');
    }

    /** {@inheritdoc} */
    public function tearDown()
    {
        $this->getContainer()->set('marello_orocommerce.rest.client_factory', $this->realRestClientFactory);
        unset(
            $this->managerRegistry,
            $this->restClient,
            $this->restClientFactory
        );
        parent::tearDown();
    }

    /**
     * @param string  $processorId
     *
     * @param Channel $channel
     * @param string  $connector
     * @param array   $parameters
     * @param array   $jobLog
     *
     * @return bool
     */
    public function runImportExportConnectorsJob(
        $processorId,
        Channel $channel,
        $connector,
        array $parameters = [],
        &$jobLog = []
    ) {
        /** @var SyncProcessor $processor */
        $processor = $this->getContainer()->get($processorId);
        $testLoggerHandler = new TestHandler(Logger::WARNING);
        $processor->getLoggerStrategy()->setLogger(new Logger('testDebug', [$testLoggerHandler]));

        $result = $processor->process($channel, $connector, $parameters);

        $jobLog = $testLoggerHandler->getRecords();

        return $result;
    }

    /**
     * @param array $jobLog
     *
     * @return string
     */
    public function formatImportExportJobLog(array $jobLog)
    {
        $output = array_reduce(
            $jobLog,
            function ($carry, $record) {
                $formatMessage = sprintf(
                    '%s> [level: %s] Message: %s',
                    PHP_EOL,
                    $record['level_name'],
                    empty($record['formatted']) ? $record['message'] : $record['formatted']
                );

                return $carry . $formatMessage;
            }
        );

        return $output;
    }

    protected function stubResources()
    {

        $this->restClient = $this->createMock(OroCommerceRestClient::class);
        $this->restClientFactory = $this->createMock(OroCommerceRestClientFactory::class);
        $this->restClientFactory
            ->expects(static::any())
            ->method('createRestClient')
            ->willReturn($this->restClient);

        $this->realRestClientFactory = $this->getContainer()->get('marello_orocommerce.rest.client_factory');
        $this->client->getContainer()->set('marello_orocommerce.rest.client_factory', $this->restClientFactory);
        /** @var OroCommerceRestTransport $transport */
        $transport = $this->getContainer()->get('marello_orocommerce.integration.transport');
        $transport->setRestClientFactory($this->restClientFactory);
    }
}
