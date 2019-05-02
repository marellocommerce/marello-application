<?php

namespace Marello\Bundle\ReturnBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloReturnBundleInstaller implements
    Installation,
    ExtendExtensionAwareInterface,
    ActivityExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var ExtendExtension */
    protected $extendExtension;
    
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_2';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloReturnReturnTable($schema);
        $this->createMarelloReturnItemTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloReturnReturnForeignKeys($schema);
        $this->addMarelloReturnItemForeignKeys($schema);

        $this->extendExtension->addEnumField(
            $schema,
            $schema->getTable('marello_return_item'),
            'reason',
            'marello_return_reason',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
            ]
        );

        $this->extendExtension->addEnumField(
            $schema,
            $schema->getTable('marello_return_item'),
            'status',
            'marello_return_status',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
            ]
        );

        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'marello_return_return');
        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'marello_return_return');
        $this->activityExtension->addActivityAssociation($schema, 'marello_notification', 'marello_return_return');
    }

    /**
     * Create marello_return_item table
     *
     * @param Schema $schema
     */
    protected function createMarelloReturnItemTable(Schema $schema)
    {
        $table = $schema->createTable('marello_return_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('return_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('quantity', 'integer', []);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['order_item_id'], 'idx_ae43aff6e76e9c94', []);
        $table->addIndex(['return_id'], 'idx_ae43aff6227416d5', []);
    }

    /**
     * Create marello_return_return table
     *
     * @param Schema $schema
     */
    protected function createMarelloReturnReturnTable(Schema $schema)
    {
        $table = $schema->createTable('marello_return_return');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_id', 'integer', ['notnull' => false]);
        $table->addColumn('return_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('sales_channel_name', 'string', ['length' => 255]);
        $table->addColumn('sales_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('locale', 'string', ['notnull' => false, 'length' => 5]);
        $table->addColumn('localization_id', 'integer', ['notnull' => false]);
        $table->addColumn('shipment_id', 'integer', ['notnull' => false]);
        $table->addColumn('return_reference', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['shipment_id'], 'UIDX_3C549D8D32C8A3DE5', []);
    }

    /**
     * Add marello_return_item foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloReturnItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_return_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_return_return'),
            ['return_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_order_item'),
            ['order_item_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add marello_return_return foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloReturnReturnForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_return_return');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_sales_channel'),
            ['sales_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_order'),
            ['order_id'],
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
     * Sets the ExtendExtension
     *
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
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
