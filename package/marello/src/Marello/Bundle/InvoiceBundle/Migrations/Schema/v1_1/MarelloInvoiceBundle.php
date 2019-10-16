<?php

namespace Marello\Bundle\InvoiceBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInvoiceBundle implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addPaymentTermColumn($schema);
        $this->addPaymentTermForeignKey($schema);
    }

    protected function addPaymentTermColumn(Schema $schema)
    {
        $table = $schema->getTable('marello_invoice_invoice');
        $table->addColumn('payment_term_id', 'integer', ['notnull' => false]);
    }

    protected function addPaymentTermForeignKey(Schema $schema)
    {
        $table = $schema->getTable('marello_invoice_invoice');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_payment_term'),
            ['payment_term_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
