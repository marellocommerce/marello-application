<?php

namespace Marello\Bundle\CoreBundle\Serializer;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Component\EntitySerializer\ConfigConverter;
use Oro\Component\EntitySerializer\ConfigNormalizer;
use Oro\Component\EntitySerializer\DataAccessorInterface as BaseDataAccessorInterface;
use Oro\Component\EntitySerializer\DataNormalizer;
use Oro\Component\EntitySerializer\DataTransformerInterface as BaseDataTransformerInterface;
use Oro\Component\EntitySerializer\DoctrineHelper;
use Oro\Component\EntitySerializer\EntitySerializer as BaseEntitySerializer;
use Oro\Component\EntitySerializer\FieldAccessor;
use Oro\Component\EntitySerializer\Filter\EntityAwareFilterInterface;
use Oro\Component\EntitySerializer\EntityConfig;
use Oro\Component\EntitySerializer\ConfigUtil;
use Oro\Component\EntitySerializer\SerializationHelper;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Serializer\ExtendEntityFieldFilter;
use Oro\Bundle\SoapBundle\Serializer\AclProtectedQueryFactory;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class EntitySerializer extends BaseEntitySerializer
{
    const WORKFLOW_ITEM_FIELD   = 'workflowItems';
    const WORKFLOW_ITEM_FQCN    = 'Oro\Bundle\WorkflowBundle\Entity\WorkflowItem';

    /** @var WorkflowManager $workflowManager */
    protected $workflowManager;

    /**
     * @param ManagerRegistry              $doctrine
     * @param ConfigManager                $configManager
     * @param BaseDataAccessorInterface    $dataAccessor
     * @param BaseDataTransformerInterface $dataTransformer
     * @param AclProtectedQueryFactory     $queryFactory
     * @param WorkflowManager              $workflowManager
     */
    public function __construct(
        ManagerRegistry $doctrine,
        ConfigManager $configManager,
        BaseDataAccessorInterface $dataAccessor,
        BaseDataTransformerInterface $dataTransformer,
        AclProtectedQueryFactory $queryFactory,
        WorkflowManager $workflowManager
    ) {
        $doctrineHelper = new DoctrineHelper($doctrine);
        $fieldAccessor  = new FieldAccessor(
            $doctrineHelper,
            $dataAccessor,
            new ExtendEntityFieldFilter($configManager)
        );

        parent::__construct(
            $doctrineHelper,
            new SerializationHelper($dataTransformer),
            $dataAccessor,
            $queryFactory,
            $fieldAccessor,
            new ConfigNormalizer(),
            new ConfigConverter(),
            new DataNormalizer()
        );

        $this->workflowManager = $workflowManager;
    }

    /**
     * @param mixed        $entity
     * @param string       $entityClass
     * @param EntityConfig $config
     * @param array        $context
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function serializeItem($entity, $entityClass, EntityConfig $config, array $context)
    {
        if (!$entity) {
            return [];
        }

        $result = [];
        $referenceFields = [];
        $entityMetadata = $this->doctrineHelper->getEntityMetadata($entityClass);
        $fields = $this->fieldAccessor->getFieldsToSerialize($entityClass, $config);
        foreach ($fields as $field) {
            $fieldConfig = $config->getField($field);
            $propertyPath = $this->getPropertyPath($field, $fieldConfig);
            $path = ConfigUtil::explodePropertyPath($propertyPath);
            $isReference = count($path) > 1;

            if (null !== $this->fieldFilter && !$isReference) {
                $isFieldAllowed = $this->fieldFilter->checkField($entity, $entityClass, $propertyPath);
                if (EntityAwareFilterInterface::FILTER_NOTHING !== $isFieldAllowed) {
                    if (EntityAwareFilterInterface::FILTER_VALUE === $isFieldAllowed) {
                        // return field but without value
                        $result[$field] = null;
                    }
                    continue;
                }
            }

            if ($isReference) {
                $referenceFields[$field] = $path;
                continue;
            }

            $value = null;
            if ($this->dataAccessor->tryGetValue($entity, $propertyPath, $value)) {
                if (null !== $value) {
                    if ($this->isAssociation($propertyPath, $entityMetadata, $fieldConfig)) {
                        if (is_object($value)) {
                            $targetConfig = $this->getTargetEntity($config, $field);
                            $targetEntityClass = $this->getAssociationTargetClass($path, $entityMetadata, $value);
                            $targetEntityId = $this->dataAccessor->getValue(
                                $value,
                                $this->doctrineHelper->getEntityIdFieldName($targetEntityClass)
                            );

                            $value = $this->serializeItem($value, $targetEntityClass, $targetConfig, $context);
                            $this->loadRelatedDataForOneEntity(
                                $value,
                                $targetEntityClass,
                                $targetEntityId,
                                $targetConfig,
                                $context
                            );

                            $postSerializeHandler = $targetConfig->getPostSerializeHandler();
                            if (null !== $postSerializeHandler) {
                                $value = $this->serializationHelper->postSerialize(
                                    $value,
                                    $postSerializeHandler,
                                    $context
                                );
                            }
                        }
                    } else {
                        $value = $this->serializationHelper->transformValue(
                            $entityClass,
                            $field,
                            $value,
                            $context,
                            $fieldConfig
                        );
                    }
                }
                $result[$field] = $value;
            } elseif ($this->fieldAccessor->isMetadataProperty($propertyPath)) {
                $result[$field] = $this->fieldAccessor->getMetadataProperty(
                    $entity,
                    $propertyPath,
                    $entityMetadata
                );
            } elseif ($propertyPath === self::WORKFLOW_ITEM_FIELD) {
                if ($this->hasWorkflowAssociation($entity) && $this->hasWorkflowItemField($config)) {
                    $workflowItems = $this->workflowManager->getWorkflowItemsByEntity($entity);
                    $targetConfig = $this->getTargetEntity($config, $field);
                    $value = $this->serializeEntities(
                        $workflowItems,
                        self::WORKFLOW_ITEM_FQCN,
                        $targetConfig,
                        $context
                    );
                    $result[$field] = $this->serializationHelper->transformValue(
                        $entityClass,
                        $field,
                        $value,
                        $context,
                        $fieldConfig
                    );
                }
            }
        }


        if (!empty($referenceFields)) {
            $result = $this->serializationHelper->handleFieldsReferencedToChildFields(
                $result,
                $entityClass,
                $config,
                $context,
                $referenceFields
            );
        }

        return $result;
    }

    /**
     * Check if the field exists in the Entity config
     * @param EntityConfig $config
     * @return bool
     */
    protected function hasWorkflowItemField(EntityConfig $config)
    {
        return $config->hasField(self::WORKFLOW_ITEM_FIELD);
    }

    /**
     * Check if there are in fact workflows on the given entity
     * @param $entity
     * @return bool
     */
    protected function hasWorkflowAssociation($entity)
    {
        return $this->workflowManager->hasWorkflowItemsByEntity($entity);
    }
}
