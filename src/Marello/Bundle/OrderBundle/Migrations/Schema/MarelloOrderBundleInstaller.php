<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloOrderBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloOrderCustomerTable($schema);
        $this->createMarelloOrderOrderTable($schema);
        $this->createMarelloOrderOrderItemTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloOrderCustomerForeignKeys($schema);
        $this->addMarelloOrderOrderForeignKeys($schema);
        $this->addMarelloOrderOrderItemForeignKeys($schema);
        $this->addMarelloAddressForeignKeys($schema);

        $table = $schema->getTable('oro_email_address');
        $table->addColumn('owner_marello_customer_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint('marello_order_customer', ['owner_marello_customer_id'], ['id']);
    }

    /**
     * Create marello_order_customer table
     *
     * @param Schema $schema
     */
    protected function createMarelloOrderCustomerTable(Schema $schema)
    {
        $table = $schema->createTable('marello_order_customer');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', []);
        $table->addColumn('address_id', 'integer', []);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);
        $table->addColumn('namePrefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('firstName', 'string', ['length' => 255]);
        $table->addColumn('middleName', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('lastName', 'string', ['length' => 255]);
        $table->addColumn('nameSuffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('email', 'text', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['address_id'], 'UNIQ_75C456C9F5B7AF75');
        $table->addIndex(['organization_id'], 'IDX_75C456C932C8A3DE', []);
    }

    /**
     * Create marello_order_order table
     *
     * @param Schema $schema
     */
    protected function createMarelloOrderOrderTable(Schema $schema)
    {
        $table = $schema->createTable('marello_order_order');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('saleschannel_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', []);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('billingaddress_id', 'integer', ['notnull' => false]);
        $table->addColumn('shippingaddress_id', 'integer', ['notnull' => false]);
        $table->addColumn('subtotal', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('saleschannel_name', 'string', ['length' => 255]);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10]);
        $table->addColumn('coupon_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('payment_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('payment_details', 'text', ['notnull' => false]);
        $table->addColumn('shipping_amount', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('shipping_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('discount_amount', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('discount_percent', 'percent', ['notnull' => false, 'comment' => '(DC2Type:percent)']);
        $table->addColumn('order_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('order_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('total_tax', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('grand_total', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('payment_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('invoiced_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['shippingaddress_id'], 'uniq_a619dd64b1835c8f');
        $table->addUniqueIndex(['order_reference', 'saleschannel_id'], 'uniq_a619dd64122432eb32758fe');
        $table->addUniqueIndex(['billingaddress_id'], 'uniq_a619dd6443656fe6');
        $table->addUniqueIndex(['order_number'], 'uniq_a619dd64551f0f81');
        $table->addUniqueIndex(['workflow_item_id'], 'uniq_a619dd641023c4ee');
        $table->addIndex(['organization_id'], 'idx_a619dd6432c8a3de', []);
        $table->addIndex(['workflow_step_id'], 'idx_a619dd6471fe882c', []);
        $table->addIndex(['saleschannel_id'], 'idx_a619dd644c7a5b2e', []);
        $table->addIndex(['customer_id'], 'IDX_A619DD649395C3F3', []);
    }

    /**
     * Create marello_order_order_item table
     *
     * @param Schema $schema
     */
    protected function createMarelloOrderOrderItemTable(Schema $schema)
    {
        $table = $schema->createTable('marello_order_order_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_id', 'integer', ['notnull' => false]);
        $table->addColumn('quantity', 'integer', []);
        $table->addColumn('price', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('tax', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('total_price', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('tax_percent', 'percent', ['notnull' => false, 'comment' => '(DC2Type:percent)']);
        $table->addColumn('discount_percent', 'percent', ['notnull' => false, 'comment' => '(DC2Type:percent)']);
        $table->addColumn('discount_amount', 'money', ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('product_name', 'string', ['length' => 255]);
        $table->addColumn('product_sku', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['order_id'], 'idx_1118665c8d9f6d38', []);
        $table->addIndex(['product_id'], 'idx_1118665c4584665a', []);
    }

    /**
     * Add marello_order_customer foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloOrderCustomerForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_order_customer');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['address_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add marello_order_order foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloOrderOrderForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_order_order');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            ['workflow_item_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_sales_channel'),
            ['saleschannel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['billingaddress_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['shippingaddress_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add marello_order_order_item foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloOrderOrderItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_order_order_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_address foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloAddressForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_address');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
