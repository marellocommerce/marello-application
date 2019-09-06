<?php

namespace Marello\Bundle\SubscriptionBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadPaymentTermData;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadSubscriptionDurationData;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadSubscriptionRenewalTypeData;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadSubscriptionSpecialPriceDurationData;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadSubscriptionTerminationNoticePeriodData;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

class MarelloSubscriptionBundleInstaller implements
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
        return 'v1_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloSubscriptionTable($schema);
        $this->createMarelloSubscriptionItemTable($schema);
        $this->updateMarelloProductTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloSubscriptionForeignKeys($schema);
    }

    /**
     * Create marello_subscription table
     *
     * @param Schema $schema
     */
    protected function createMarelloSubscriptionTable(Schema $schema)
    {
        $table = $schema->createTable('marello_subscription');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('subscription_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('billing_address_id', 'integer', ['notnull' => true]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => true]);
        $table->addColumn('start_date', 'datetime', ['notnull' => false]);
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'duration',
            LoadSubscriptionDurationData::ENUM_CLASS,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $table->addColumn('termination_date', 'datetime', ['notnull' => false]);
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'terminationNoticePeriod',
            LoadSubscriptionTerminationNoticePeriodData::ENUM_CLASS,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $table->addColumn('cancel_before_duration', 'boolean', ['notnull' => true]);
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'renewalType',
            LoadSubscriptionRenewalTypeData::ENUM_CLASS,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $table->addColumn('shipping_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('shipping_method_type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('payment_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('payment_freq', 'integer', ['notnull' => false]);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('sales_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('item_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['customer_id'], 'IDX_A619DD649395C3F31', []);
        $table->addIndex(['billing_address_id'], 'IDX_A619DD6443656FE61', []);
        $table->addIndex(['shipping_address_id'], 'IDX_A619DD64B1835C8F1', []);
        $table->addIndex(['sales_channel_id'], 'IDX_A619DD644C7A5B2E1', []);
        $table->addIndex(['organization_id']);
        $table->addUniqueIndex(['item_id'], 'UNIQ_75C456C9F5B7AF751134');
        $table->addUniqueIndex(['subscription_number'], 'UNIQ_D411FA7F5F6607D3');

        $this->activityExtension->addActivityAssociation($schema, 'marello_notification', $table->getName());
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', $table->getName());
        $this->activityExtension->addActivityAssociation($schema, 'oro_note', $table->getName());
    }

    /**
     * Create marello_subscription_item table
     *
     * @param Schema $schema
     */
    protected function createMarelloSubscriptionItemTable(Schema $schema)
    {
        $table = $schema->createTable('marello_subscription_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('sku', 'string', ['length' => 255]);
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'duration',
            LoadSubscriptionDurationData::ENUM_CLASS,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $table->addColumn('price', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn(
            'special_price',
            'money',
            ['notnull' => false, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'specialPriceDuration',
            LoadSubscriptionSpecialPriceDurationData::ENUM_CLASS,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    protected function updateMarelloProductTable(Schema $schema)
    {
        $table = $schema->getTable('marello_product_product');
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'subscriptionDuration',
            LoadSubscriptionDurationData::ENUM_CLASS,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
                'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_HIDDEN]
            ]
        );
        $table->addColumn(
            'number_of_deliveries',
            'integer',
            [
                'notnull' => false,
                'oro_options' => [
                    'extend'    => ['owner' => ExtendScope::OWNER_SYSTEM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_HIDDEN],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'paymentTerm',
            LoadPaymentTermData::ENUM_CLASS,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
                'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_HIDDEN]
            ]
        );
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'specialPriceDuration',
            LoadSubscriptionSpecialPriceDurationData::ENUM_CLASS,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
                'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_HIDDEN]
            ]
        );
    }

    /**
     * Add marello_subscription foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSubscriptionForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_subscription');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
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
            ['sales_channel_id'],
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
            $schema->getTable('marello_subscription_item'),
            ['item_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
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
