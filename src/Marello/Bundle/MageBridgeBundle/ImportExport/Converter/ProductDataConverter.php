<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 03/04/2018
 * Time: 15:05
 */

namespace Marello\Bundle\MageBridgeBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\DefaultDataConverter;

class ProductDataConverter extends DefaultDataConverter
{
    const PRODUCT_TYPE_SIMPLE = 'simple';
    const PRODUCT_TYPE_CONFIGURABLE = 'configurable';
    const DEFAULT_ATTRIBUTE_SET_ID = 4;
    const DEFAULT_CATALOG_VISIBILITY = 4;
    const DEFAULT_TAX_CLASS_ID = 2;

    protected $mageStatus = [ 'enabled' => 1, 'disabled' => 2];

    /**
     * {@inheritDoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $name = $exportedRecord['name'];
        $productData = [
            'type_id'           => self::PRODUCT_TYPE_SIMPLE,
            'attribute_set_id'  => self::DEFAULT_ATTRIBUTE_SET_ID,
            'sku'               => $exportedRecord['sku'],
            'weight'            => $exportedRecord['weight'],
            'status'            => $this->mageStatus[$exportedRecord['status']['name']],
            'visibility'        => self::DEFAULT_CATALOG_VISIBILITY,
            'name'              => $exportedRecord['name'],
            'description'       => $name,
            'short_description' => $name,
            'price'             => (float)$exportedRecord['price']['value'],
            'tax_class_id'      => self::DEFAULT_TAX_CLASS_ID,
        ];
        return $productData;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        return $this->convertToComplexData($importedRecord, $skipNullValues);
    }

}