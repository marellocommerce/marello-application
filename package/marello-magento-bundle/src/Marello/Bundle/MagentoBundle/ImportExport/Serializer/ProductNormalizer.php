<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

class ProductNormalizer extends ConfigurableEntityNormalizer
{
    use TraitEntityNormalizer;

    /**
     * @param string $entityName
     * @param string $fieldName
     * @param array $context
     * @return bool
     */
    protected function isFieldSkippedForNormalization($entityName, $fieldName, array $context)
    {
        $allowedFields = [];
        if (in_array($fieldName, $allowedFields)) {
            return parent::isFieldSkippedForNormalization($entityName, $fieldName, $context);
        }

        // Do not normalize non identity fields for short mode
        $isNotIdentity = $this->getMode($context) === self::SHORT_MODE
            && !$this->fieldHelper->getConfigValue($entityName, $fieldName, 'identity');

        return $isNotIdentity;
    }
}
