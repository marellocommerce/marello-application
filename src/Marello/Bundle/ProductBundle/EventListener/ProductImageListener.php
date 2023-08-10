<?php

namespace Marello\Bundle\ProductBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\ProductBundle\Async\ProductImageUpdateProcessor;
use Marello\Bundle\ProductBundle\Entity\Repository\ProductRepository;

class ProductImageListener
{
    public function __construct(
        protected ConfigManager $configManager,
        protected AttachmentManager $attachmentManager,
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
            if (!empty($unitOfWork->getScheduledEntityInsertions())) {
                $records = $this->filterRecords($unitOfWork->getScheduledEntityInsertions());
                $this->applyCallBackForChangeSet('updateImageFileExternalUrl', $records);
            }
            if (!empty($unitOfWork->getScheduledEntityUpdates())) {
                $records = $this->filterRecords($unitOfWork->getScheduledEntityUpdates());
                $this->applyCallBackForChangeSet('updateImageFileExternalUrl', $records);
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
     */
    protected function updateImageFileExternalUrl(File $file): void
    {
        if (Product::class === $file->getParentEntityClass()) {
            $url = $this->attachmentManager
                ->getFilteredImageUrl($file, 'product_view');
            $file->setMediaUrl($url);
        }
    }

    /**
     * @param ConfigUpdateEvent $event
     * @return void
     */
    public function onConfigUpdate(ConfigUpdateEvent $event): void
    {
        if (!$event->isChanged('marello_product.image_use_external_url')) {
            return;
        }

        foreach ($this->getProductsToProcess() as $product) {
            // if the setting is changed, we need to update all the images
            // of products to regenerate the media urls
            $this->messageProducer->send(
                ProductImageUpdateProcessor::TOPIC,
                ['productSku' => $product['sku']]
            );
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
            ->setSQLLogger(null); //turn off log

        $qb = $em->createQueryBuilder('p');
        $query = $qb->select('p.id', 'p.sku');

        return $query->getQuery()->toIterable();
    }
}
