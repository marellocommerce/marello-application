<?php

namespace Marello\Bundle\PackingBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloPackingBundleInstaller implements Installation, ActivityExtensionAwareInterface
{
    const MARELLO_PACKING_SLIP_TABLE = 'marello_packing_packing_slip';
    const MARELLO_PACKING_SLIP_ITEM_TABLE = 'marello_packing_pack_slip_item';

    /**
     * @var ActivityExtension
     */
    protected $activityExtension;

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
        $this->createMarelloPackingSlipTable($schema);
        $this->createMarelloPackingSlipItemTable($schema);
        
        $this->addMarelloPackingSlipForeignKeys($schema);
        $this->addMarelloPackingSlipItemForeignKeys($schema);
    }

    /**
     * Create marello_shipment table
     *
     * @param Schema $schema
     */
    protected function createMarelloPackingSlipTable(Schema $schema)
    {
        $table = $schema->createTable(self::MARELLO_PACKING_SLIP_TABLE);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => true]);
        $table->addColumn('order_id', 'integer', ['notnull' => true]);
        $table->addColumn('billing_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('salesChannel_id', 'integer', ['notnull' => false]);
        $table->addColumn('warehouse_id', 'integer', ['notnull' => false]);
        $table->addColumn('comment', 'text', ['notnull' => false]);
        $table->addColumn('packing_slip_number', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        
        $this->activityExtension->addActivityAssociation($schema, 'oro_note', self::MARELLO_PACKING_SLIP_TABLE);
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', self::MARELLO_PACKING_SLIP_TABLE);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloPackingSlipItemTable(Schema $schema)
    {
        $table = $schema->createTable(self::MARELLO_PACKING_SLIP_ITEM_TABLE);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('packing_slip_id', 'integer', ['notnull' => true]);
        $table->addColumn('product_id', 'integer', ['notnull' => true]);
        $table->addColumn('product_name', 'string', ['length' => 255]);
        $table->addColumn('product_sku', 'string', ['length' => 255]);
        $table->addColumn('order_item_id', 'integer', []);
        $table->addColumn('weight', 'float', ['notnull' => true]);
        $table->addColumn('quantity', 'float', ['notnull' => true]);
        $table->addColumn('comment', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['order_item_id'], 'UNIQ_DBF8FC2AE415FB15', []);
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloPackingSlipForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::MARELLO_PACKING_SLIP_TABLE);
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
            $schema->getTable('marello_address'),
            ['billing_address_id'],
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
            $schema->getTable('marello_sales_sales_channel'),
            ['salesChannel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
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
    protected function addMarelloPackingSlipItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::MARELLO_PACKING_SLIP_ITEM_TABLE);
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
            $schema->getTable(self::MARELLO_PACKING_SLIP_TABLE),
            ['packing_slip_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * @param ActivityExtension $activityExtension
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }
}
