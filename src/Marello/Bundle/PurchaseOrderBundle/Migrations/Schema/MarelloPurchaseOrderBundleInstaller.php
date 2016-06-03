<?php

namespace Marello\Bundle\PurchaseOrderBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloPurchaseOrderBundleInstaller implements Installation, NoteExtensionAwareInterface
{
    /** @var  NoteExtension */
    protected $noteExtension;

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
        $this->createMarelloPurchaseOrderTable($schema);
        $this->createMarelloPurchaseOrderItemTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloPurchaseOrderForeignKeys($schema);
        $this->addMarelloPurchaseOrderItemForeignKeys($schema);

        $this->noteExtension->addNoteAssociation($schema, 'marello_purchase_order');
    }

    /**
     * Create marello_purchase_order table
     *
     * @param Schema $schema
     */
    protected function createMarelloPurchaseOrderTable(Schema $schema)
    {
        $table = $schema->createTable('marello_purchase_order');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', []);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('purchaseOrderNumber', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_34E72AC31023C4EE');
        $table->addIndex(['workflow_step_id'], 'IDX_34E72AC371FE882C', []);
        $table->addIndex(['organization_id'], 'IDX_34E72AC332C8A3DE', []);
    }

    /**
     * Create marello_purchase_order_item table
     *
     * @param Schema $schema
     */
    protected function createMarelloPurchaseOrderItemTable(Schema $schema)
    {
        $table = $schema->createTable('marello_purchase_order_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('product_id', 'integer', ['notnull' => false]);
        $table->addColumn('order_id', 'integer', ['notnull' => false]);
        $table->addColumn('productSku', 'string', ['length' => 255]);
        $table->addColumn('productName', 'string', ['length' => 255]);
        $table->addColumn('orderedAmount', 'integer', []);
        $table->addColumn('receivedAmount', 'integer', []);
        $table->addColumn('status', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['product_id'], 'IDX_3483BD864584665A', []);
        $table->addIndex(['order_id'], 'IDX_3483BD868D9F6D38', []);
    }

    /**
     * Add marello_purchase_order foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloPurchaseOrderForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_purchase_order');
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
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_purchase_order_item foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloPurchaseOrderItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_purchase_order_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_purchase_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Sets the NoteExtension
     *
     * @param noteExtension $noteExtension
     */
    public function setNoteExtension(NoteExtension $noteExtension)
    {
        $this->noteExtension = $noteExtension;
    }
}
