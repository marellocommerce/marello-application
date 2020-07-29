<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Marello\Bundle\Magento2Bundle\Entity\IntegrationAwareInterface;
use Marello\Bundle\Magento2Bundle\Entity\OriginAwareInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class DefaultMagento2ImportStrategy extends ConfigurableAddOrReplaceStrategy implements
    LoggerAwareInterface,
    StepExecutionAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Uses to get info about all existed records of specific type,
     * to remove records that non exist anymore in remote system.
     */
    public const CONTEXT_ORIGIN_IDS_OF_IMPORTED_RECORDS = 'originIdsOfImportedRecords';

    /** @var bool */
    protected $enableCollectingOriginIds = false;

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param bool $enableCollectingOriginIds
     */
    public function setEnableCollectingOriginIds(bool $enableCollectingOriginIds)
    {
        $this->enableCollectingOriginIds = $enableCollectingOriginIds;
    }

    /**
     * @param PropertyAccessor $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritDoc}
     */
    public function process($entity)
    {
        if ($this->enableCollectingOriginIds && $entity instanceof OriginAwareInterface && $entity->getOriginId()) {
            $this->appendDataToContext(
                self::CONTEXT_ORIGIN_IDS_OF_IMPORTED_RECORDS,
                $entity->getOriginId()
            );
        }

        return parent::process($entity);
    }

    /**
     * Specify channel as identity field
     *
     * @param string $entityName
     * @param array $identityValues
     * @return null|object
     */
    protected function findEntityByIdentityValues($entityName, array $identityValues)
    {
        if (!empty($identityValues) && is_a($entityName, IntegrationAwareInterface::class, true)) {
            $identityValues['channel'] = $this->context->getOption('channel');
        }

        return parent::findEntityByIdentityValues($entityName, $identityValues);
    }

    /**
     * Combine channel with identity values for entity search on local new entities storage
     *
     * @param       $entity
     * @param       $entityClass
     * @param array $searchContext
     *
     * @return array|null
     */
    protected function combineIdentityValues($entity, $entityClass, array $searchContext)
    {
        if (!empty($searchContext) && is_a($entityClass, IntegrationAwareInterface::class, true)) {
            $searchContext['channel'] = $this->context->getOption('channel');
        }

        return parent::combineIdentityValues($entity, $entityClass, $searchContext);
    }

    /**
     * @return ExecutionContext
     */
    protected function getExecutionContext()
    {
        if (!$this->stepExecution) {
            throw new \InvalidArgumentException('Execution context is not configured');
        }

        return $this->stepExecution->getJobExecution()->getExecutionContext();
    }

    /**
     * @param string $contextKey
     * @param mixed $dataToAppend
     */
    protected function appendDataToContext(string $contextKey, $dataToAppend)
    {
        $data = (array) $this->getExecutionContext()->get($contextKey);
        $data[] = $dataToAppend;
        $this->getExecutionContext()->put($contextKey, $data);
    }
}
