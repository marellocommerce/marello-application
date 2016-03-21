<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloOrderBundleInstaller implements Installation, ActivityExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

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

        $this->activityExtension->addActivityAssociation($schema, 'marello_notification', 'marello_order_order');
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
        $table->addColumn('organization_id', 'integer', []);
        $table->addColumn('shippingaddress_id', 'integer', ['notnull' => false]);
        $table->addColumn('billingaddress_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('saleschannel_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('subtotal', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('saleschannel_name', 'string', ['length' => 255]);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10]);
        $table->addColumn('coupon_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('payment_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('payment_details', 'text', ['notnull' => false]);
        $table->addColumn(
            'shipping_amount',
            'money',
            ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('shipping_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn(
            'discount_amount',
            'money',
            ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('discount_percent', 'percent', ['notnull' => false, 'comment' => '(DC2Type:percent)']);
        $table->addColumn('order_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('order_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('total_tax', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('grand_total', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('payment_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('invoiced_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addIndex(['organization_id'], 'idx_a619dd6432c8a3de', []);
        $table->addUniqueIndex(['shippingaddress_id'], 'uniq_a619dd64b1835c8f');
        $table->addIndex(['workflow_step_id'], 'idx_a619dd6471fe882c', []);
        $table->addUniqueIndex(['order_reference', 'saleschannel_id'], 'uniq_a619dd64122432eb32758fe');
        $table->addUniqueIndex(['billingaddress_id'], 'uniq_a619dd6443656fe6');
        $table->addIndex(['saleschannel_id'], 'idx_a619dd644c7a5b2e', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['order_number'], 'uniq_a619dd64551f0f81');
        $table->addUniqueIndex(['workflow_item_id'], 'uniq_a619dd641023c4ee');
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
        $table->addColumn('total_price', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('tax_percent', 'percent', ['notnull' => false, 'comment' => '(DC2Type:percent)']);
        $table->addColumn('discount_percent', 'percent', ['notnull' => false, 'comment' => '(DC2Type:percent)']);
        $table->addColumn(
            'discount_amount',
            'money',
            ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('product_name', 'string', ['length' => 255]);
        $table->addColumn('product_sku', 'string', ['length' => 255]);
        $table->addIndex(['order_id'], 'idx_1118665c8d9f6d38', []);
        $table->addIndex(['product_id'], 'idx_1118665c4584665a', []);
        $table->setPrimaryKey(['id']);
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
            $schema->getTable('oro_organization'),
            ['organization_id'],
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
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_sales_channel'),
            ['saleschannel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            ['workflow_item_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
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

    /**
     * Sets the ActivityExtension
     *
     * @param ActivityExtension $activityExtension
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }
}
