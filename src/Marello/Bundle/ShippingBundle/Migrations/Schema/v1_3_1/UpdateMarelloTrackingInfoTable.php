<?php

namespace Marello\Bundle\ShippingBundle\Migrations\Schema\v1_3_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateMarelloTrackingInfoTable implements Migration
{
    /**
     * @param Schema $schema
     * @param QueryBag $queries
     * @return void
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloTrackingInfoTable($schema);
        $this->updateMarelloShipmentTable($schema);
        $this->addMarelloTrackingInfoForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateMarelloTrackingInfoTable(Schema $schema)
    {
        $table = $schema->getTable('marello_tracking_info');
        if (!$table->hasColumn('created_at')) {
            $table->addColumn('created_at', 'datetime');
        }

        if (!$table->hasColumn('updated_at')) {
            $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        }

        if (!$table->hasColumn('shipment_id')) {
            $table->addColumn('shipment_id', 'integer', ['notnull' => true]);
            $table->addUniqueIndex(['shipment_id'], 'marello_tracking_info_shipmentidx');
        }
    }

    protected function updateMarelloShipmentTable(Schema $schema)
    {
        $table = $schema->getTable('marello_shipment');
        $table->addUniqueIndex(['tracking_info_id'], 'marello_shipment_trackinginfoidx');
    }

    /**
     * Add marello_tracking_info foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloTrackingInfoForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_tracking_info');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_shipment'),
            ['shipment_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
