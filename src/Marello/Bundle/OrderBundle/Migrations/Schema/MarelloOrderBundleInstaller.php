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
        $this->createMarelloOrderOrderTable($schema);
        $this->createMarelloOrderOrderItemTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloOrderOrderForeignKeys($schema);
        $this->addMarelloOrderOrderItemForeignKeys($schema);
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
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('saleschannel_id', 'integer', ['notnull' => false]);
        $table->addColumn('shippingaddress_id', 'integer', ['notnull' => false]);
        $table->addColumn('billingaddress_id', 'integer', ['notnull' => false]);
        $table->addColumn('ordernumber', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('orderreference', 'integer', ['notnull' => false]);
        $table->addColumn('subtotal', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('totaltax', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('grandtotal', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['saleschannel_id'], 'idx_a619dd644c7a5b2e', []);
        $table->addUniqueIndex(['billingaddress_id'], 'uniq_a619dd6443656fe6');
        $table->addUniqueIndex(['ordernumber'], 'uniq_a619dd64989a8203');
        $table->addIndex(['workflow_step_id'], 'idx_a619dd6471fe882c', []);
        $table->addUniqueIndex(['workflow_item_id'], 'uniq_a619dd641023c4ee');
        $table->addUniqueIndex(['shippingaddress_id'], 'uniq_a619dd64b1835c8f');
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
        $table->addColumn('order_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_id', 'integer', ['notnull' => false]);
        $table->addColumn('quantity', 'integer', []);
        $table->addColumn('price', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('tax', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('totalprice', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addIndex(['order_id'], 'idx_1118665c8d9f6d38', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['product_id'], 'idx_1118665c4584665a', []);
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
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            ['workflow_item_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_sales_channel'),
            ['saleschannel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['shippingaddress_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['billingaddress_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => null]
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
            $schema->getTable('marello_order_order'),
            ['order_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }
}
