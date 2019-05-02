<?php

namespace Marello\Bundle\ManualShippingBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloManualShippingBundle implements Migration
{
    /**
     * @param Schema $schema
     * @param QueryBag $queries
     *
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloManualShippingTransportLabelTable($schema);
        $this->addMarelloManualShippingTransportLabelForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    private function createMarelloManualShippingTransportLabelTable(Schema $schema)
    {
        if (!$schema->hasTable('marello_man_ship_transp_label')) {
            $table = $schema->createTable('marello_man_ship_transp_label');

            $table->addColumn('transport_id', 'integer', []);
            $table->addColumn('localized_value_id', 'integer', []);

            $table->setPrimaryKey(['transport_id', 'localized_value_id']);
            $table->addIndex(['transport_id'], 'marello_manual_ship_transport_label_transport_id', []);
            $table->addUniqueIndex(
                ['localized_value_id'],
                'marello_manual_ship_transport_label_localized_value_id',
                []
            );
        }
    }

    /**
     * @param Schema $schema
     *
     * @throws SchemaException
     */
    private function addMarelloManualShippingTransportLabelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_man_ship_transp_label');

        if (!$table->hasForeignKey('localized_value_id')) {
            $table->addForeignKeyConstraint(
                $schema->getTable('oro_fallback_localization_val'),
                ['localized_value_id'],
                ['id'],
                ['onDelete' => 'CASCADE', 'onUpdate' => null]
            );
        }

        if (!$table->hasForeignKey('transport_id')) {
            $table->addForeignKeyConstraint(
                $schema->getTable('oro_integration_transport'),
                ['transport_id'],
                ['id'],
                ['onDelete' => 'CASCADE', 'onUpdate' => null]
            );
        }
    }
}
