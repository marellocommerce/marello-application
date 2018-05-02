<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 03/04/2018
 * Time: 15:05
 */

namespace Marello\Bundle\MageBridgeBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\DefaultDataConverter;

class StoreDataConverter extends DefaultDataConverter
{
    /**
     * {@inheritDoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        return [];
    }

}
