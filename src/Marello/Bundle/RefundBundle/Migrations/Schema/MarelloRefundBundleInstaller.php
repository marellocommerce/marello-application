<?php

namespace Marello\Bundle\RefundBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloRefundBundleInstaller implements Installation
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
        $this->createMarelloRefundTable($schema);
        $this->createMarelloRefundItemTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloRefundForeignKeys($schema);
        $this->addMarelloRefundItemForeignKeys($schema);
    }

    /**
     * Create marello_refund table
     *
     * @param Schema $schema
     */
    protected function createMarelloRefundTable(Schema $schema)
    {
        $table = $schema->createTable('marello_refund');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', []);
        $table->addColumn('customer_id', 'integer', []);
        $table->addColumn('order_id', 'integer', []);
        $table->addColumn('refundNumber', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('refundAmount', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('workflowItem_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflowStep_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['refundNumber'], 'UNIQ_973FA8836E8C706D');
        $table->addIndex(['customer_id'], 'IDX_973FA8839395C3F3', []);
        $table->addIndex(['order_id'], 'IDX_973FA8838D9F6D38', []);
        $table->addIndex(['organization_id'], 'IDX_A619DD6432C8A3DE', []);
        $table->addIndex(['workflowItem_id'], 'IDX_973FA8835E43682', []);
        $table->addIndex(['workflowStep_id'], 'IDX_973FA88364397A40', []);
    }

    /**
     * Create marello_refund_item table
     *
     * @param Schema $schema
     */
    protected function createMarelloRefundItemTable(Schema $schema)
    {
        $table = $schema->createTable('marello_refund_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('refund_id', 'integer', []);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('quantity', 'integer', []);
        $table->addColumn('baseAmount', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('refundAmount', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('orderItem_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['refund_id'], 'IDX_2D9010DD189801D5', []);
        $table->addIndex(['orderItem_id'], 'IDX_2D9010DDE76E9C94', []);
    }

    /**
     * Add marello_refund foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloRefundForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_refund');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            ['workflowItem_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflowStep_id'],
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
            $schema->getTable('marello_order_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add marello_refund_item foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloRefundItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_refund_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_refund'),
            ['refund_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_order_order_item'),
            ['orderItem_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
