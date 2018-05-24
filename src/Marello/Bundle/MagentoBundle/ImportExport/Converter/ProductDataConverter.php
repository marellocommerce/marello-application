<?php

namespace Marello\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class ProductDataConverter extends IntegrationAwareDataConverter
{
    const PRODUCT_TYPE_SIMPLE = 'simple';
    const PRODUCT_TYPE_CONFIGURABLE = 'configurable';
    const DEFAULT_ATTRIBUTE_SET_ID = 4;
    const DEFAULT_CATALOG_VISIBILITY = 4;
    const DEFAULT_TAX_CLASS_ID = 2;
    const DEFAULT_PRODUCT_STATUS = 2; //disabled in magento


    protected $mageStatus = [ 'enabled' => 1, 'disabled' => 2];
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'product_id'        => 'originId',
            'sku'               => 'sku',
            'name'              => 'name',
            'type'              => 'type',
            'price'             => 'price',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $result = parent::convertToImportFormat($importedRecord, $skipNullValues);

        $dateObj = new \DateTime('now', new \DateTimeZone('UTC'));
        $date = $dateObj->format('Y-m-d H:i:s');
        $result['createdAt'] = $date;
        $result['updatedAt'] = $date;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        //TODO: change $productData specific for update / create e.g status removed for update
        $sku = $exportedRecord['sku'];
        $name = $exportedRecord['name'];
        $productData = [
            'weight'            => $exportedRecord['weight'],
            'status'            => self::DEFAULT_PRODUCT_STATUS,
            'visibility'        => self::DEFAULT_CATALOG_VISIBILITY,
            'name'              => $name,
            'description'       => $name,
            'short_description' => $name,
            'tax_class_id'      => self::DEFAULT_TAX_CLASS_ID,
        ];

        return [
            'productData' => $productData,
            'sku' => $sku,
            'set' => self::DEFAULT_ATTRIBUTE_SET_ID,
            'type' => self::PRODUCT_TYPE_SIMPLE
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }
}
