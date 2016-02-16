<?php

namespace Marello\Bundle\ReturnBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloReturnBundleInstaller implements Installation, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

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
        $table->addIndex(['order_id'], 'idx_3c549d8d8d9f6d38', []);
        $table->addUniqueIndex(['workflow_item_id'], 'uniq_3c549d8d1023c4ee');
        $table->addIndex(['workflow_step_id'], 'idx_3c549d8d71fe882c', []);
        $table->setPrimaryKey(['id']);
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
        $table->addColumn('orderitem_id', 'integer', ['notnull' => false]);
        $table->addColumn('quantity', 'integer', []);
        $table->addColumn('createdat', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updatedat', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addIndex(['orderitem_id'], 'idx_ae43aff6e76e9c94', []);
        $table->addIndex(['return_id'], 'idx_ae43aff6227416d5', []);
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
            $schema->getTable('marello_return_return'),
            ['return_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_order_item'),
            ['orderitem_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => null]
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
}
