<?php

namespace Marello\Bundle\CoreBundle\Provider;

use Oro\Bundle\AttachmentBundle\Provider\AttachmentEntityConfigProviderInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;

class AttachmentEntityConfigProviderDecorator implements AttachmentEntityConfigProviderInterface
{
    public function __construct(
        protected AttachmentEntityConfigProviderInterface $innerProvider
    ) {}

    public function getFieldConfig(string $entityClass, string $fieldName): ?ConfigInterface
    {
        if (!$entityClass) {
            return null;
        }

        return $this->innerProvider->getFieldConfig($entityClass, $fieldName);
    }

    public function getEntityConfig(string $entityClass): ?ConfigInterface
    {
        if (!$entityClass) {
            return null;
        }

        return $this->innerProvider->getEntityConfig($entityClass);
    }
}
