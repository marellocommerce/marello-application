<?php

namespace Marello\Bundle\InvoiceBundle\Migrations\Schema\v3_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateInvoiceTable implements Migration
{
    const INVOICE_TABLE_NAME = 'marello_invoice_invoice';

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateInvoiceTable($schema);
    }

    /**
     * {@inheritdoc}
     * @param Schema $schema
     * @param QueryBag $queries
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateInvoiceTable(Schema $schema)
    {
        $table = $schema->getTable(self::INVOICE_TABLE_NAME);
        if (!$table->hasColumn('saleschannel_name')) {
            $table->addColumn('saleschannel_name', 'string', ['notnull' => false, 'length' => 255]);
        }
    }
}
