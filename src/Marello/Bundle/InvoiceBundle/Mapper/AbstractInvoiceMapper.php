<?php

namespace Marello\Bundle\InvoiceBundle\Mapper;

use Marello\Bundle\InvoiceBundle\Entity\InvoiceType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

abstract class AbstractInvoiceMapper implements MapperInterface
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var EntityFieldProvider
     */
    protected $entityFieldProvider;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var array
     */
    protected $mappedFields = [];

    /**
     * @param EntityFieldProvider $entityFieldProvider
     * @param PropertyAccessorInterface $propertyAccessor
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        EntityFieldProvider $entityFieldProvider,
        PropertyAccessorInterface $propertyAccessor,
        DoctrineHelper $doctrineHelper
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->entityFieldProvider = $entityFieldProvider;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param object $sourceEntity
     * @param string $targetEntityClass
     * @return array
     */
    protected function getData($sourceEntity, $targetEntityClass)
    {
        $result = [];
        $mapFields = $this->getMapFields($targetEntityClass);
        foreach ($mapFields as $field) {
            try {
                $value = $this->propertyAccessor->getValue($sourceEntity, $field);
                $result[$field] = $value;
            } catch (NoSuchPropertyException $e) {
            }
        }

        return $result;
    }

    /**
     * @param object $entity
     * @param array $data
     */
    protected function assignData($entity, array $data)
    {
        foreach ($data as $name => $value) {
            try {
                $this->propertyAccessor->setValue($entity, $name, $value);
            } catch (NoSuchPropertyException $e) {
            }
        }
    }

    /**
     * @param string $entityClass
     * @return string[]
     */
    protected function getMapFields($entityClass)
    {
        if (isset($this->mappedFields[$entityClass])) {
            $fields = $this->mappedFields[$entityClass];
        } else {
            $fields = $this->entityFieldProvider->getFields($entityClass, true, true, false, false, true, false);
            $this->mappedFields[$entityClass] = $fields;
        }

        $withoutIds = array_filter(
            $fields,
            function ($field) {
                return empty($field['identifier']);
            }
        );

        return array_column($withoutIds, 'name');
    }

    /**
     * @param string $type
     * @return InvoiceType
     */
    protected function getInvoiceType($type)
    {
        return $this->doctrineHelper
            ->getEntityManagerForClass(InvoiceType::class)
            ->getRepository(InvoiceType::class)
            ->find($type);
    }
}
