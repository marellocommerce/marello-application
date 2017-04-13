<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloOrderBundle implements
    Migration,
    ActivityExtensionAwareInterface,
    AttachmentExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var  AttachmentExtension */
    protected $attachmentExtension;

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
        $this->addMarelloOrderCustomerOwnerToOroEmailAddress($schema);

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'marello_order_order');
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
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
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
        $table->addColumn('primary_address_id', 'integer', []);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('email', 'text', []);
        $table->addColumn('tax_identification_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['primary_address_id'], 'UNIQ_75C456C9F5B7AF75');
        $table->addIndex(['organization_id'], 'IDX_75C456C932C8A3DE', []);

        $this->attachmentExtension->addAttachmentAssociation($schema, $table->getName());
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
        $table->addColumn('organization_id', 'integer', []);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('order_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('invoice_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('subtotal', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('total_tax', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('grand_total', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10]);
        $table->addColumn('payment_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('payment_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('payment_details', 'text', ['notnull' => false]);
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
        $table->addColumn('invoiced_at', 'datetime', ['notnull' => false]);
        $table->addColumn('saleschannel_name', 'string', ['length' => 255]);
        $table->addColumn('billing_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('salesChannel_id', 'integer', ['notnull' => false]);
        $table->addColumn('localization_id', 'integer', ['notnull' => false]);
        $table->addColumn('locale', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('shipment_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['order_number'], 'UNIQ_A619DD64551F0F81');
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_A619DD641023C4EE');
        $table->addUniqueIndex(['order_reference', 'salesChannel_id'], 'UNIQ_A619DD64122432EB32758FE');
        $table->addIndex(['customer_id'], 'IDX_A619DD649395C3F3', []);
        $table->addIndex(['billing_address_id'], 'IDX_A619DD6443656FE6', []);
        $table->addIndex(['shipping_address_id'], 'IDX_A619DD64B1835C8F', []);
        $table->addIndex(['salesChannel_id'], 'IDX_A619DD644C7A5B2E', []);
        $table->addIndex(['workflow_step_id'], 'IDX_A619DD6471FE882C', []);
        $table->addIndex(['organization_id'], 'IDX_A619DD6432C8A3DE', []);

        $this->activityExtension->addActivityAssociation($schema, 'marello_notification', $table->getName());
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', $table->getName());
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
        $table->setPrimaryKey(['id']);
        $table->addIndex(['product_id'], 'IDX_1118665C4584665A', []);
        $table->addIndex(['order_id'], 'IDX_1118665C8D9F6D38', []);
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
            ['primary_address_id'],
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
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
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
     * Add owner_marello_customer_id to oro_email_address table.
     *
     * @param Schema $schema
     */
    protected function addMarelloOrderCustomerOwnerToOroEmailAddress(Schema $schema)
    {
        $table = $schema->getTable('oro_email_address');
        $table->addColumn('owner_marello_customer_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint('marello_order_customer', ['owner_marello_customer_id'], ['id']);
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
}
