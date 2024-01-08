<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;

use Marello\Bundle\OrderBundle\Model\OrderStatusesInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloOrderBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface,
    AttachmentExtensionAwareInterface,
    ExtendExtensionAwareInterface
{
    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**
     * @var AttachmentExtension
     */
    protected $attachmentExtension;

    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v3_1_5';
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
        
        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'marello_order_order');
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
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('order_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('invoice_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('subtotal', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('total_tax', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('grand_total', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('locale_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10]);
        $table->addColumn('payment_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn(
            'payment_method_options',
            'json_array',
            [
                'notnull' => false, 'comment' => '(DC2Type:json_array)'
            ]
        );
        $table->addColumn('data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
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
        $table->addColumn('shipping_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('shipping_method_type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('shipping_method_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('shipping_method_details', 'text', ['notnull' => false]);
        $table->addColumn(
            'estimated_shipping_cost_amount',
            'money',
            ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'override_shipping_cost_amount',
            'money',
            ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'discount_amount',
            'money',
            [
                'notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)'
            ]
        );
        $table->addColumn('discount_percent', 'percent', ['notnull' => false, 'comment' => '(DC2Type:percent)']);
        $table->addColumn('coupon_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('purchase_date', 'datetime', ['notnull' => false]);
        $table->addColumn('invoiced_at', 'datetime', ['notnull' => false]);
        $table->addColumn('saleschannel_name', 'string', ['length' => 255]);
        $table->addColumn('billing_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('salesChannel_id', 'integer', ['notnull' => false]);
        $table->addColumn('localization_id', 'integer', ['notnull' => false]);
        $table->addColumn('shipment_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['shipment_id'], 'UNIQ_A619DD647BE036FC');
        $table->addUniqueIndex(['order_number'], 'UNIQ_A619DD64551F0F81');
        $table->addUniqueIndex(['order_reference', 'salesChannel_id'], 'UNIQ_A619DD64122432EB32758FE');
        $table->addIndex(['customer_id'], 'IDX_A619DD649395C3F3', []);
        $table->addIndex(['billing_address_id'], 'IDX_A619DD6443656FE6', []);
        $table->addIndex(['shipping_address_id'], 'IDX_A619DD64B1835C8F', []);
        $table->addIndex(['salesChannel_id'], 'IDX_A619DD644C7A5B2E', []);
        $table->addColumn('delivery_date', 'datetime', ['notnull' => false]);
        $table->addColumn('order_note', 'text', ['notnull' => false]);
        $table->addColumn('po_number', 'string', ['length' => 255, 'notnull' => false]);
        $table->addIndex(['organization_id']);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addIndex(['user_owner_id']);

        $this->activityExtension->addActivityAssociation($schema, 'marello_notification', $table->getName());
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', $table->getName());
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'orderStatus',
            OrderStatusesInterface::ORDER_STATUS_ENUM_CLASS,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
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
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_name', 'string', ['length' => 255]);
        $table->addColumn('product_sku', 'string', ['length' => 255]);
        $table->addColumn('quantity', 'integer', []);
        $table->addColumn('price', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn(
            'original_price_incl_tax',
            'money',
            [
                'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)', 'notnull' => false
            ]
        );
        $table->addColumn(
            'original_price_excl_tax',
            'money',
            [
                'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)', 'notnull' => false
            ]
        );
        $table->addColumn(
            'purchase_price_incl',
            'money',
            [
                'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)', 'notnull' => false
            ]
        );
        $table->addColumn('tax', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('tax_percent', 'percent', ['notnull' => false, 'comment' => '(DC2Type:percent)']);
        $table->addColumn('discount_percent', 'percent', ['notnull' => false, 'comment' => '(DC2Type:percent)']);
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
        $table->addColumn('tax_code_id', 'integer', ['notnull' => false]);
        $table->addColumn('allocation_exclusion', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('item_type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addIndex(['user_owner_id']);

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'status',
            'marello_item_status',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'productUnit',
            'marello_product_unit',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id']);
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
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
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
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_address'),
            ['shipping_address_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_localization'),
            ['localization_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('marello_shipment'),
            ['shipment_id'],
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
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tax_tax_code'),
            ['tax_code_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
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

    /**
     * Sets the AttachmentExtension
     *
     * @param AttachmentExtension $attachmentExtension
     */
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }

    /**
     * Sets the ExtendExtension
     *
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
