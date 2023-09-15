<?php

namespace Marello\Bundle\ProductBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Async\Topic\ProductImageUpdateTopic;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

class ProductImageListener
{
    public function __construct(
        protected ConfigManager $configManager,
        protected MessageProducerInterface $messageProducer,
        protected DoctrineHelper $doctrineHelper
    ) {
    }

    /**
     * @param OnFlushEventArgs $args
     * @return void
     * @throws \Exception
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        if ($this->configManager->get('marello_product.image_use_external_url')) {
            $entityManager = $args->getEntityManager();
            $unitOfWork = $entityManager->getUnitOfWork();
            if (!empty($unitOfWork->getScheduledEntityUpdates())) {
                $records = $this->filterRecords($unitOfWork->getScheduledEntityUpdates());
                $this->applyCallBackForChangeSet('updateImageFileExternalUrl', $records);
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     * @return void
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        if ($this->configManager->get('marello_product.image_use_external_url')) {
            $entity = $args->getObject();
            if ($entity instanceof Product && $entity->getImage()) {
                $this->updateImageFileExternalUrl($entity->getImage());
            }
        }
    }

    /**
     * @param array $records
     * @return array
     */
    protected function filterRecords(array $records): array
    {
        return array_filter($records, [$this, 'getIsEntityInstanceOf']);
    }

    /**
     * @param $entity
     * @return bool
     */
    public function getIsEntityInstanceOf($entity): bool
    {
        return ($entity instanceof File);
    }

    /**
     * @param $callback
     * @param array $changeSet
     * @return void
     * @throws \Exception
     */
    protected function applyCallBackForChangeSet($callback, array $changeSet): void
    {
        try {
            array_walk($changeSet, [$this, $callback]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param File $file
     * @return void
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    protected function updateImageFileExternalUrl(File $file): void
    {
        if (Product::class === $file->getParentEntityClass()) {
            $this->sendToMessageProducer($file->getParentEntityId());
        }
    }

    /**
     * @param ConfigUpdateEvent $event
     * @return void
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    public function onConfigUpdate(ConfigUpdateEvent $event): void
    {
        if (!$event->isChanged('marello_product.image_use_external_url')) {
            return;
        }

        if (!$event->getNewValue('marello_product.image_use_external_url')) {
            return;
        }

        foreach ($this->getProductsToProcess() as $product) {
            // if the setting is changed, we need to update all the images
            // of products to regenerate the media urls
            $this->sendToMessageProducer($product['id']);
        }
    }

    /**
     * @return iterable
     */
    protected function getProductsToProcess(): iterable
    {
        /** @var ProductRepository $qb */
        $em = $this->doctrineHelper->getEntityRepositoryForClass(Product::class);
        $this->doctrineHelper
            ->getEntityManager(Product::class)
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger(); //turn off log

        $qb = $em->createQueryBuilder('p');
        $query = $qb->select('p.id', 'p.sku');

        return $query->getQuery()->toIterable();
    }

    /**
     * @param int $productId
     * @return void
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    protected function sendToMessageProducer(int $productId): void
    {
        $this->messageProducer->send(
            ProductImageUpdateTopic::getName(),
            ['productId' => $productId]
        );
    }
}
