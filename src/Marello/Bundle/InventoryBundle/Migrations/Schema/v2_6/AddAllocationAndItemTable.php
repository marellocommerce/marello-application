<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;

class AddAllocationAndItemTable implements Migration, ActivityExtensionAwareInterface, ExtendExtensionAwareInterface
{
    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloInventoryAllocation($schema);
        $this->createMarelloInventoryAllocationItem($schema);
        $this->addMarelloInventoryAllocationForeignKeys($schema);
        $this->addMarelloInventoryAllocationItemForeignKeys($schema);
        $this->addAllocationActivities($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryAllocation(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_allocation');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('allocation_number', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_id', 'integer', ['notnull' => true]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('warehouse_id', 'integer', ['notnull' => false]);
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addColumn('type', 'string', ['notnull' => false]);
        $table->addColumn('comment', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);

        $table->setPrimaryKey(['id']);

        $tableName = $this->extendExtension->getNameGenerator()->generateEnumTableName('marello_allocation_status');
        // enum table is already available and created...
        if ($schema->hasTable($tableName)) {
            return;
        }

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'status',
            'marello_allocation_status',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );

        $tableName = $this->extendExtension->getNameGenerator()->generateEnumTableName('marello_allocation_state');
        // enum table is already available and created...
        if ($schema->hasTable($tableName)) {
            return;
        }

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'state',
            'marello_allocation_state',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloInventoryAllocationItem(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_alloc_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('allocation_id', 'integer', ['notnull' => true]);
        $table->addColumn('warehouse_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_id', 'integer', ['notnull' => true]);
        $table->addColumn('product_name', 'string', ['length' => 255]);
        $table->addColumn('product_sku', 'string', ['length' => 255]);
        $table->addColumn('order_item_id', 'integer', []);
        $table->addColumn('quantity', 'float', ['notnull' => true]);
        $table->addColumn('quantity_confirmed', 'float', ['notnull' => false]);
        $table->addColumn('quantity_rejected', 'float', ['notnull' => false]);
        $table->addColumn('comment', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id']);
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloInventoryAllocationForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_allocation');
        $table->addForeignKeyConstraint(
            $table,
            ['parent_id'],
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
            $schema->getTable('marello_order_order'),
            ['order_id'],
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
            $schema->getTable('marello_inventory_warehouse'),
            ['warehouse_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloInventoryAllocationItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_alloc_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_order_item'),
            ['order_item_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_allocation'),
            ['allocation_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['warehouse_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
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
     * Sets the ExtendExtension
     *
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addAllocationActivities(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_allocation');
        $this->activityExtension->addActivityAssociation($schema, 'marello_notification', $table->getName());
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', $table->getName());
    }
}
