<?php

namespace Marello\Bundle\InvoiceBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloInvoiceBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v3_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloInvoiceInvoiceTable($schema);
        $this->createMarelloInvoiceInvoiceItemTable($schema);
        $this->createMarelloInvoicePaymentsTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloInvoiceInvoiceForeignKeys($schema);
        $this->addMarelloInvoiceInvoiceItemForeignKeys($schema);
        $this->addMarelloInvoicePaymentsForeignKeys($schema);
    }

    /**
     * Create marello_invoice_invoice table
     *
     * @param Schema $schema
     */
    protected function createMarelloInvoiceInvoiceTable(Schema $schema)
    {
        $table = $schema->createTable('marello_invoice_invoice');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('invoice_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('billing_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('invoiced_at', 'datetime', ['notnull' => false]);
        $table->addColumn('invoice_due_date', 'datetime', ['notnull' => false]);
        $table->addColumn('payment_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('shipping_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('shipping_method_type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('order_id', 'integer', ['notnull' => true]);
        $table->addColumn('payment_term_id', 'integer', ['notnull' => false]);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10]);
        $table->addColumn('type', 'string', ['notnull' => true]);
        $table->addColumn('invoice_type', 'string', ['notnull' => false]);
        $table->addColumn('status', 'string', ['notnull' => false, 'length' => 10]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('salesChannel_id', 'integer', ['notnull' => false]);
        $table->addColumn('saleschannel_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('subtotal', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('total_tax', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('grand_total', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('total_due', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('total_paid', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn(
            'shipping_amount_incl_tax',
            'money',
            [
                'notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)'
            ]
        );
        $table->addColumn(
            'shipping_amount_excl_tax',
            'money',
            [
                'notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)'
            ]
        );
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['invoice_number'], null);
        $table->addIndex(['order_id'], null);
        $table->addIndex(['customer_id'], null, []);
        $table->addIndex(['billing_address_id'], null, []);
        $table->addIndex(['shipping_address_id'], null, []);
        $table->addIndex(['salesChannel_id'], null, []);
        $table->addIndex(['organization_id']);
    }

    /**
     * Create marello_invoice_invoice_item table
     *
     * @param Schema $schema
     */
    protected function createMarelloInvoiceInvoiceItemTable(Schema $schema)
    {
        $table = $schema->createTable('marello_invoice_invoice_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('invoice_item_type', 'string', []);
        $table->addColumn('invoice_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_name', 'string', ['length' => 255]);
        $table->addColumn('product_sku', 'string', ['length' => 255]);
        $table->addColumn('quantity', 'integer', []);
        $table->addColumn('price', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('tax', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn(
            'discount_amount',
            'money',
            [
                'notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)'
            ]
        );
        $table->addColumn(
            'row_total_incl_tax',
            'money',
            [
                'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)'
            ]
        );
        $table->addColumn(
            'row_total_excl_tax',
            'money',
            [
                'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)'
            ]
        );
        // cannot add extend product unit option because of inheritance table and the related entities it facilitates
        // see vendor/oro/platform/src/Oro/Bundle/EntityExtendBundle/Migration/ExtendOptionsBuilder.php#166,
        // the count of entityClassnames is > 1 so it will throw an error, for now we will workaround this by using a simple string column
        $table->addColumn('product_unit', 'string', ['length' => 255, 'notnull' => false]);

        $table->setPrimaryKey(['id']);
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
     * Add marello_invoice_invoice foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInvoiceInvoiceForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_invoice_invoice');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_payment_term'),
            ['payment_term_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['billing_address_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['shipping_address_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_sales_channel'),
            ['salesChannel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_customer_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
    
    /**
     * Add marello_invoice_invoice_item foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloInvoiceInvoiceItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_invoice_invoice_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_invoice_invoice'),
            ['invoice_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
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
