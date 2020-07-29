<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Remover;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Marello\Bundle\Magento2Bundle\Entity\Repository\NotInOriginIdsInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class NonExistedRecordsRemover implements ItemWriterInterface, StepExecutionAwareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var ContextRegistry */
    protected $contextRegistry;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param ManagerRegistry $registry
     * @param ContextRegistry $contextRegistry
     */
    public function __construct(ManagerRegistry $registry, ContextRegistry $contextRegistry)
    {
        $this->registry = $registry;
        $this->contextRegistry = $contextRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function write(array $items)
    {
        $channelId = $this->getChannelId();
        $existedOriginIds = reset($items);
        if(empty($existedOriginIds) || null === $channelId) {
            return;
        }

        $entityManager = $this->getEntityManager();
        if (null === $entityManager) {
            return;
        }

        $repository = $this->getRepository($entityManager);
        if (null === $repository) {
            return;
        }

        try {
            $entities = $repository->getEntitiesNotInOriginIdsInGivenIntegration($existedOriginIds, $channelId);
            if (!empty($entities)) {
                foreach ($entities as $index => $entity) {
                    $entityManager->remove($entity);
                    if ($index + 1 % 100 === 0) {
                        $entityManager->flush();
                    }
                }

                $entityManager->flush();
                $entityManager->clear();
            }
        } catch (RetryableException $e) {
            $context = $this->contextRegistry->getByStepExecution($this->stepExecution);
            $context->setValue('deadlockDetected', true);
        }
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @return ObjectManager|null
     */
    protected function getEntityManager(): ?ObjectManager
    {
        $className = $this->getContext()->getOption('entityName');
        if (null === $className) {
            $this->logger->notice(
                '[Magento 2] Trying to call NonExistedRecordsRemover with non properly configured context. ' .
                'Expected to have "entityName" in context, but got null.'
            );

            return null;
        }

        return $this->registry->getManagerForClass($className);
    }

    /**
     * @param ObjectManager $manager
     * @return NotInOriginIdsInterface|null
     */
    protected function getRepository(ObjectManager $manager): ?NotInOriginIdsInterface
    {
        $className = $this->getContext()->getOption('entityName');
        if (null === $className) {
            $this->logger->notice(
                '[Magento 2] Trying to call NonExistedRecordsRemover with non properly configured context. ' .
                'Expected to have "entityName" in context, but got null.'
            );

            return null;
        }

        $repository = $manager->getRepository($className);

        if (!$repository instanceof NotInOriginIdsInterface) {
            $this->logger->notice(
                '[Magento 2] Trying to call NonExistedRecordsRemover with non properly configured context. ' .
                'Expected that entity has repository instanceof RemoveNonExistedRecordsNotInOriginIdsInterface,' .
                'but given doesn\'t implemented it.',
                [
                    'entityName' => $className,
                    'repositoryType' => is_object($repository) ? get_class($repository) : gettype($repository)
                ]
            );

            return null;
        }

        return $repository;
    }

    /**
     * @return int|null
     */
    protected function getChannelId(): ?int
    {
        return $this->getContext()->getOption('channel');
    }

    /**
     * @return ContextInterface
     */
    protected function getContext()
    {
        return $this->contextRegistry->getByStepExecution($this->stepExecution);
    }
}
