<?php

namespace Marello\Bundle\ShippingBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloShippingBundle implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloTrackingInfoTable($schema);
        $this->updateMarelloShipmentTable($schema);

        $this->addMarelloShipmentForeignKeys($schema);
    }

    protected function createMarelloTrackingInfoTable(Schema $schema)
    {
        $table = $schema->createTable('marello_tracking_info');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('tracking_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('track_trace_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('tracking_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('provider', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('provider_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateMarelloShipmentTable(Schema $schema)
    {
        $table = $schema->getTable('marello_shipment');
        $table->addColumn('tracking_info_id', 'integer', ['notnull' => false]);
    }

    /**
     * @param Schema $schema
     * @return void
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addMarelloShipmentForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_shipment');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_tracking_info'),
            ['tracking_info_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
