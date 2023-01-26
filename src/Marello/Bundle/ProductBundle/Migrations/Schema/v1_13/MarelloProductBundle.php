<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_13;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

use Marello\Bundle\ProductBundle\Migrations\Schema\MarelloProductBundleInstaller;

class MarelloProductBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Add barcode attribute */
        $this->addBarcode($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function addBarcode(Schema $schema)
    {
        $table = $schema->getTable(MarelloProductBundleInstaller::PRODUCT_TABLE);
        if (!$table->hasColumn('barcode')) {
            $table->addColumn('barcode', 'string', ['length' => 255, 'notnull' => false]);
        }
    }
}
