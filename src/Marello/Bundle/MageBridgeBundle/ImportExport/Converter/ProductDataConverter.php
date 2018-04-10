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
    /**
     * {@inheritDoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        return $this->convertToPlainData($exportedRecord, $skipNullValues);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        return $this->convertToComplexData($importedRecord, $skipNullValues);
    }

}