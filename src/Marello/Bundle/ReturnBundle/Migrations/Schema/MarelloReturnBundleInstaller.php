<?php

namespace Marello\Bundle\ReturnBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloReturnBundleInstaller implements Installation
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
        $this->createMarelloReturnReturnTable($schema);
        $this->createMarelloReturnItemTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloReturnReturnForeignKeys($schema);
        $this->addMarelloReturnItemForeignKeys($schema);
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
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_id', 'integer', ['notnull' => false]);
        $table->addColumn('returnnumber', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('createdat', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updatedat', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addIndex(['workflow_step_id'], 'idx_3c549d8d71fe882c', []);
        $table->addIndex(['order_id'], 'idx_3c549d8d8d9f6d38', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['workflow_item_id'], 'uniq_3c549d8d1023c4ee');
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
        $table->addColumn('orderitem_id', 'integer', ['notnull' => false]);
        $table->addColumn('return_id', 'integer', ['notnull' => false]);
        $table->addColumn('sku', 'string', ['length' => 255]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('quantity', 'integer', []);
        $table->addColumn('createdat', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updatedat', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addIndex(['return_id'], 'idx_ae43aff6227416d5', []);
        $table->addIndex(['orderitem_id'], 'idx_ae43aff6e76e9c94', []);
        $table->setPrimaryKey(['id']);
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
            $schema->getTable('marello_order_order'),
            ['order_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => null]
        );
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
            $schema->getTable('marello_order_order_item'),
            ['orderitem_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_return_return'),
            ['return_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => null]
        );
    }
}
