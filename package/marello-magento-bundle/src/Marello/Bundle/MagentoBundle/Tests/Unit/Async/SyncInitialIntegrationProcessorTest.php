<?php

namespace Marello\Bundle\MagentoBundle\Tests\Unit\Async;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Psr\Log\LoggerInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Marello\Bundle\MagentoBundle\Async\SyncInitialIntegrationProcessor;
use Marello\Bundle\MagentoBundle\Async\Topics;
use Marello\Bundle\MagentoBundle\Provider\InitialSyncProcessor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;
use Oro\Bundle\SearchBundle\Engine\IndexerInterface;

use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ClassExtensionTrait;

class SyncInitialIntegrationProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    /** @var SyncInitialIntegrationProcessor */
    private $processor;

    /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;

    /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $entityRepository;

    /** @var InitialSyncProcessor|\PHPUnit_Framework_MockObject_MockObject */
    private $initialSyncProcessor;

    /** @var OptionalListenerManager|\PHPUnit_Framework_MockObject_MockObject */
    private $optionalListenerManager;

    /** @var JobRunner */
    private $jobRunner;

    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock
            ->expects($this->any())
            ->method('getConfiguration')
            ->willReturn(new Configuration());

        $this->entityManager = $this->createMock(EntityManager::class);
        $this->entityManager
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);

        $this->entityRepository = $this->createMock(EntityRepository::class);

        $doctrine = $this->createMock(DoctrineHelper::class);
        $doctrine
            ->expects($this->any())
            ->method('getEntityManager')
            ->with(Integration::class)
            ->willReturn($this->entityManager);
        $doctrine
            ->expects($this->any())
            ->method('getEntityRepository')
            ->with(Channel::class)
            ->willReturn($this->entityRepository);

        $this->initialSyncProcessor = $this->createMock(InitialSyncProcessor::class);
        $this->initialSyncProcessor
            ->expects($this->any())
            ->method('getLoggerStrategy')
            ->willReturn(new LoggerStrategy());

        $this->optionalListenerManager = $this->createMock(OptionalListenerManager::class);
        $this->jobRunner = new JobRunner();
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new SyncInitialIntegrationProcessor(
            $doctrine,
            $this->initialSyncProcessor,
            $this->optionalListenerManager,
            $this->jobRunner,
            $this->createMock(IndexerInterface::class),
            $this->createMock(TokenStorageInterface::class),
            $this->logger
        );
    }

    public function testShouldImplementMessageProcessorInterface()
    {
        $this->assertClassImplements(MessageProcessorInterface::class, SyncInitialIntegrationProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, SyncInitialIntegrationProcessor::class);
    }

    public function testShouldSubscribeOnSyncInitialIntegrationTopic()
    {
        $this->assertEquals([Topics::SYNC_INITIAL_INTEGRATION], SyncInitialIntegrationProcessor::getSubscribedTopics());
    }

    public function testShouldLogAndRejectIfMessageBodyMissIntegrationId()
    {
        $message = new NullMessage();
        $message->setBody('[]');

        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have integrationId set');

        $this->optionalListenerManager
            ->expects($this->never())
            ->method('disableListener');
        $this->optionalListenerManager
            ->expects($this->never())
            ->method('disableListeners');
        $this->optionalListenerManager
            ->expects($this->never())
            ->method('enableListener');
        $this->optionalListenerManager
            ->expects($this->never())
            ->method('enableListeners');

        $status = $this->processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The malformed json given.
     */
    public function testThrowIfMessageBodyInvalidJson()
    {
        $message = new NullMessage();
        $message->setBody('[}');

        $this->processor->process($message, new NullSession());
    }

    public function testShouldRejectMessageIfIntegrationNotExist()
    {
        $message = new NullMessage();
        $message->setBody(JSON::encode(['integration_id' => 'theIntegrationId']));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Integration not found: theIntegrationId');

        $this->optionalListenerManager
            ->expects($this->never())
            ->method('disableListener');
        $this->optionalListenerManager
            ->expects($this->never())
            ->method('enableListener');

        $status = $this->processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectMessageIfIntegrationIsNotEnabled()
    {
        $integration = new Integration();
        $integration->setEnabled(false);
        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Integration::class)
            ->willReturn($integration);

        $message = new NullMessage();
        $message->setBody(JSON::encode(['integration_id' => 'theIntegrationId']));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Integration is not enabled: theIntegrationId');

        $this->optionalListenerManager
            ->expects($this->never())
            ->method('disableListener');
        $this->optionalListenerManager
            ->expects($this->never())
            ->method('enableListener');

        $status = $this->processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }
}
