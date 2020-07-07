<?php

namespace Marello\Bundle\InvoiceBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateInvoiceItemTable implements Migration
{
    const INVOICE_ITEM_TABLE_NAME = 'marello_invoice_invoice_item';

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateInvoiceItemTable($schema);
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     * @param QueryBag $queries
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateInvoiceItemTable(Schema $schema)
    {
        $table = $schema->getTable(self::INVOICE_ITEM_TABLE_NAME);
        // cannot add extend product unit option because of inheritance table and the related entities it facilitates
        // see vendor/oro/platform/src/Oro/Bundle/EntityExtendBundle/Migration/ExtendOptionsBuilder.php#166,
        // the count of entityClassnames is > 1 so it will throw an error, for now we will workaround this by using a simple string column
        $table->addColumn('product_unit', 'string', ['length' => 255, 'notnull' => false]);
    }
}
