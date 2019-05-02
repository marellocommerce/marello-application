<?php

namespace Marello\Bundle\RefundBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloRefundBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

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
        $this->createMarelloRefundTable($schema);
        $this->createMarelloRefundItemTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloRefundForeignKeys($schema);
        $this->addMarelloRefundItemForeignKeys($schema);

        $this->activityExtension->addActivityAssociation($schema, 'marello_notification', 'marello_refund');
        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'marello_refund');
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
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', []);
        $table->addColumn('order_id', 'integer', []);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10]);
        $table->addColumn('refund_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('refund_amount', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('localization_id', 'integer', ['notnull' => false]);
        $table->addColumn('locale', 'string', ['notnull' => false, 'length' => 5]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['refund_number'], 'UNIQ_973FA8836E8C706D');
        $table->addIndex(['customer_id'], 'IDX_973FA8839395C3F3', []);
        $table->addIndex(['order_id'], 'IDX_973FA8838D9F6D38', []);
        $table->addIndex(['organization_id']);
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
        $table->addColumn('base_amount', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('refund_amount', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('order_item_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['refund_id'], 'IDX_2D9010DD189801D5', []);
        $table->addIndex(['order_item_id'], 'IDX_2D9010DDE76E9C94', []);
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
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_localization'),
            ['localization_id'],
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
            ['order_item_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
