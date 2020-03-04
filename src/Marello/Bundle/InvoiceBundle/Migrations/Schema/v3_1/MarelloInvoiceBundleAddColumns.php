<?php

namespace Marello\Bundle\InvoiceBundle\Migrations\Schema\v3_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloInvoiceBundleAddColumns implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateInvoiceTable($schema);
        if (!$schema->hasTable('marello_invoice_payments')) {
            $this->createMarelloInvoicePaymentsTable($schema);
            $this->addMarelloInvoicePaymentsForeignKeys($schema);
        }
    }

    /**
     * @param Schema $schema
     */
    public function updateInvoiceTable(Schema $schema)
    {
        $table = $schema->getTable('marello_invoice_invoice');
        $table->addColumn('total_due', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('total_paid', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
    }

    /**
     * Create marello_invoice_invoice_item table
     *
     * @param Schema $schema
     */
    protected function createMarelloInvoicePaymentsTable(Schema $schema)
    {
        $table = $schema->createTable('marello_invoice_payments');
        $table->addColumn('invoice_id', 'integer', ['notnull' => true]);
        $table->addColumn('payment_id', 'integer', ['notnull' => true]);
        $table->addUniqueIndex(['payment_id'], null);
        $table->setPrimaryKey(['invoice_id', 'payment_id']);
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloInvoicePaymentsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_invoice_payments');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_invoice_invoice'),
            ['invoice_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_payment_payment'),
            ['payment_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
