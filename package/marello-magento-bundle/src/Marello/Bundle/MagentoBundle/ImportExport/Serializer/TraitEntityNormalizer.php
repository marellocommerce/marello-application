<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Serializer;

trait TraitEntityNormalizer
{
    /**
     * @var string
     */
    protected $entityClass;

    protected $addtionalFields = [];

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return is_a($data, $this->entityClass);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return is_a($type, $this->entityClass, true);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $result = parent::normalize($object, $format, $context);
        foreach ($this->addtionalFields as $fieldName) {
            $result[$fieldName] = $this->getObjectValue($object, $fieldName);
        }
        return $result;
    }
}
