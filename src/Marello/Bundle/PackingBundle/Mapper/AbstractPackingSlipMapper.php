<?php

namespace Marello\Bundle\PackingBundle\Mapper;

use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

abstract class AbstractPackingSlipMapper implements MapperInterface
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
     * @var array
     */
    protected $mappedFields = [];

    /**
     * @param EntityFieldProvider $entityFieldProvider
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(
        EntityFieldProvider $entityFieldProvider,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->entityFieldProvider = $entityFieldProvider;
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
            $fields = $this->entityFieldProvider->getFields($entityClass, true, true, false, true, true, false);
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
}
