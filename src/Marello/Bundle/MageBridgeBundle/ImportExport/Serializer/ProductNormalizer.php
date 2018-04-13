<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 22-8-17
 * Time: 13:38
 */

namespace Marello\Bundle\MageBridgeBundle\ImportExport\Serializer;

use Marello\Bundle\MageBridgeBundle\Provider\Serializer\TraitEntityNormalizer;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;

class ProductNormalizer extends ConfigurableEntityNormalizer
{
    use TraitEntityNormalizer;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $result = parent::normalize($object, $format, $context);

        //TODO: M1 has currency setup per website && price per website


        return $result;
    }

    /**
     * @param string $entityName
     * @param string $fieldName
     * @param array $context
     * @return bool
     */
    protected function isFieldSkippedForNormalization($entityName, $fieldName, array $context)
    {
        /*
        $allowedFields = [
            'sku',
            'name',
            'prices',
            'weight',
            'status',
            'taxCode',
            'salesChannelTaxCodes',
            'variant',
            'image',
            'categories',
            'cost',
            'channels'
        ];
        if (in_array($fieldName, $allowedFields)) {
            return parent::isFieldSkippedForNormalization($entityName, $fieldName, $context);
        }*/
        // Do not normalize non identity fields for short mode
        $isNotIdentity = $this->getMode($context) === self::SHORT_MODE
            && !$this->fieldHelper->getConfigValue($entityName, $fieldName, 'identity');

        return $isNotIdentity;
    }
}
