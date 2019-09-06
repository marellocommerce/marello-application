<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloEnterpriseReplenishmentBundle implements Migration, ActivityExtensionAwareInterface
{
    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloReplenishmentOrderConfigTable($schema);
        $this->createMarelloReplenishmentOrderTable($schema);
        $this->createMarelloReplenishmentOrderItemTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloReplenishmentOrderConfigForeignKeys($schema);
        $this->addMarelloReplenishmentOrderForeignKeys($schema);
        $this->addMarelloReplenishmentOrderItemForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloReplenishmentOrderConfigTable(Schema $schema)
    {
        $table = $schema->createTable('marello_repl_order_config');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('origins', 'json_array', ['notnull' => true, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('destinations', 'json_array', ['notnull' => true, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('products', 'json_array', ['notnull' => true, 'comment' => '(DC2Type:json_array)']);
        $table->addColumn('strategy', 'string', ['length' => 50, 'notnull' => true]);
        $table->addColumn('execution_date_time', 'datetime', ['notnull' => false]);
        $table->addColumn('percentage', 'float', ['notnull' => true]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id']);
    }


    /**
     * Create marello_order_order table
     *
     * @param Schema $schema
     */
    protected function createMarelloReplenishmentOrderTable(Schema $schema)
    {
        $table = $schema->createTable('marello_repl_order');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('repl_order_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('origin_id', 'integer', ['notnull' => true]);
        $table->addColumn('destination_id', 'integer', ['notnull' => true]);
        $table->addColumn('execution_date_time', 'datetime', ['notnull' => false]);
        $table->addColumn('percentage', 'float', ['notnull' => true]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('ro_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('repl_order_config_id', 'integer', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['repl_order_number'], 'UNIQ_A619DD647BE036FC11');
        $table->addUniqueIndex(['ro_code'], 'UNIQ_A619DD647BE036FC21');
        $table->addIndex(['organization_id']);
    }

    /**
     * Create marello_order_order_item table
     *
     * @param Schema $schema
     */
    protected function createMarelloReplenishmentOrderItemTable(Schema $schema)
    {
        $table = $schema->createTable('marello_repl_order_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('repl_order_id', 'integer', ['notnull' => true]);
        $table->addColumn('product_id', 'integer', ['notnull' => true]);
        $table->addColumn('product_name', 'string', ['length' => 255]);
        $table->addColumn('product_sku', 'string', ['length' => 255]);
        $table->addColumn('note', 'text', ['notnull' => false]);
        $table->addColumn('inventory_qty', 'integer', ['notnull' => false]);
        $table->addColumn('total_inventory_qty', 'integer', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloReplenishmentOrderConfigForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_repl_order_config');
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
    protected function addMarelloReplenishmentOrderForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_repl_order');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['origin_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_inventory_warehouse'),
            ['destination_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_repl_order_config'),
            ['repl_order_config_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloReplenishmentOrderItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_repl_order_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_repl_order'),
            ['repl_order_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
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
