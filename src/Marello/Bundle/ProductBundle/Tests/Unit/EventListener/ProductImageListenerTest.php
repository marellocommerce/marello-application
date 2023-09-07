<?php

namespace Marello\Bundle\ProductBundle\Tests\Unit\EventListener;

use Doctrine\ORM\UnitOfWork;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Configuration;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\EntityManagerInterface;

use PHPUnit\Framework\TestCase;

use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\EventListener\ProductImageListener;

class ProductImageListenerTest extends TestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManagerMock */
    private $configManagerMock;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManagerMock */
    private $messageProducerMock;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelperMock */
    private $doctrineHelperMock;

    /** @var  ProductImageListener $productImageListener */
    private $productImageListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->configManagerMock = $this->createMock(ConfigManager::class);
        $this->messageProducerMock = $this->createMock(MessageProducerInterface::class);
        $this->doctrineHelperMock = $this->createMock(DoctrineHelper::class);
        $this->productImageListener = new ProductImageListener(
            $this->configManagerMock,
            $this->messageProducerMock,
            $this->doctrineHelperMock
        );
    }

    /**
     * {@inheritdoc}
     */
    public function testMediaUrlNotUpdated()
    {
        $this->configManagerMock
            ->expects(static::once())
            ->method('get')
            ->with('marello_product.image_use_external_url')
            ->willReturn(false);

        $this->messageProducerMock
            ->expects(static::never())
            ->method('send');

        /** @var OnFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $eventPostFlushArgs */
        $eventPostFlushArgs = $this->createMock(OnFlushEventArgs::class);
        $eventPostFlushArgs
            ->expects(static::never())
            ->method('getEntityManager');

        $this->productImageListener->onFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function testNoInsertionsOrUpdates()
    {
        $this->configManagerMock
            ->expects(static::once())
            ->method('get')
            ->with('marello_product.image_use_external_url')
            ->willReturn(true);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $unitOfWorkMock = $this->createMock(UnitOfWork::class);
        /** @var OnFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $eventPostFlushArgs */
        $eventPostFlushArgs = $this->createMock(OnFlushEventArgs::class);
        $eventPostFlushArgs
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($entityManagerMock);

        $entityManagerMock
            ->expects(static::once())
            ->method('getUnitOfWork')
            ->willReturn($unitOfWorkMock);

        $unitOfWorkMock
            ->expects(static::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);

        $unitOfWorkMock
            ->expects(static::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);

        $this->productImageListener->onFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function testMediaUrlUpdated()
    {
        $this->configManagerMock
            ->expects(static::once())
            ->method('get')
            ->with('marello_product.image_use_external_url')
            ->willReturn(true);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $unitOfWorkMock = $this->createMock(UnitOfWork::class);
        /** @var OnFlushEventArgs|\PHPUnit\Framework\MockObject\MockObject $eventPostFlushArgs */
        $eventPostFlushArgs = $this->createMock(OnFlushEventArgs::class);
        $eventPostFlushArgs
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($entityManagerMock);

        $entityManagerMock
            ->expects(static::once())
            ->method('getUnitOfWork')
            ->willReturn($unitOfWorkMock);

        $file = $this->createMock(File::class);
        $file
            ->expects(static::once())
            ->method('getParentEntityClass')
            ->willReturn(Product::class);

        $file
            ->expects(static::once())
            ->method('getParentEntityId')
            ->willReturn(1);

        $this->messageProducerMock
            ->expects(static::once())
            ->method('send');

        $unitOfWorkMock
            ->expects(static::exactly(2))
            ->method('getScheduledEntityInsertions')
            ->willReturn([$file]);

        $unitOfWorkMock
            ->expects(static::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);

        $this->productImageListener->onFlush($eventPostFlushArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function testOnConfigUpdateNotEnabled()
    {
        $this->messageProducerMock
            ->expects(static::never())
            ->method('send');
        $configUpdateEvent = $this->createMock(ConfigUpdateEvent::class);
        $configUpdateEvent
            ->expects(static::once())
            ->method('isChanged')
            ->with('marello_product.image_use_external_url')
            ->willReturn(false);

        $this->productImageListener->onConfigUpdate($configUpdateEvent);
    }

    /**
     * {@inheritdoc}
     */
    public function testOnConfigUpdateBeingDisabledNoUpdateForImageProcessor()
    {
        $this->messageProducerMock
            ->expects(static::never())
            ->method('send');
        $configUpdateEvent = $this->createMock(ConfigUpdateEvent::class);
        $configUpdateEvent
            ->expects(static::once())
            ->method('isChanged')
            ->with('marello_product.image_use_external_url')
            ->willReturn(true);

        $configUpdateEvent
            ->expects(static::once())
            ->method('getNewValue')
            ->with('marello_product.image_use_external_url')
            ->willReturn(false);

        $this->productImageListener->onConfigUpdate($configUpdateEvent);
    }


    /**
     * {@inheritdoc}
     */
    public function testOnConfigUpdateProductsSendToProducer()
    {
        $this->messageProducerMock
            ->expects(static::atLeastOnce())
            ->method('send');

        $entityRepositoryMock = $this->createMock(EntityRepository::class);

        $this->doctrineHelperMock
            ->expects(static::once())
            ->method('getEntityRepositoryForClass')
            ->with(Product::class)
            ->willReturn($entityRepositoryMock);

        $entityManagerMock = $this->createMock(EntityManager::class);
        $this->doctrineHelperMock
            ->expects(static::once())
            ->method('getEntityManager')
            ->willReturn($entityManagerMock);

        $connectionMock = $this->createMock(Connection::class);
        $entityManagerMock
            ->expects(static::once())
            ->method('getConnection')
            ->willReturn($connectionMock);

        $configurationMock = $this->createMock(Configuration::class);
        $connectionMock
            ->expects(static::once())
            ->method('getConfiguration')
            ->willReturn($configurationMock);

        $configurationMock
            ->expects(static::once())
            ->method('setSQLLogger')
            ->with(null);

        $queryBuilder = $this->createMock(QueryBuilder::class);

        $entityRepositoryMock
            ->expects(static::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(static::once())
            ->method('select')
            ->with('p.id', 'p.sku')
            ->willReturn($queryBuilder);

        $query = $this->createMock(AbstractQuery::class);
        $queryBuilder
            ->expects(static::once())
            ->method('getQuery')
            ->willReturn($query);

        $query
            ->expects(static::once())
            ->method('toIterable')
            ->willReturn([['id' => 1, 'sku' => 'p1234']]);

        $configUpdateEvent = $this->createMock(ConfigUpdateEvent::class);
        $configUpdateEvent
            ->expects(static::once())
            ->method('isChanged')
            ->with('marello_product.image_use_external_url')
            ->willReturn(true);

        $configUpdateEvent
            ->expects(static::once())
            ->method('getNewValue')
            ->with('marello_product.image_use_external_url')
            ->willReturn(true);

        $this->productImageListener->onConfigUpdate($configUpdateEvent);
    }
}
