<?php

namespace Marello\Bundle\UPSBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloUPSBundle implements Migration
{
    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOroIntegrationTransportTable($schema);
        $this->createMarelloUPSShippingServiceTable($schema);
        $this->createMarelloUPSTransportShipServiceTable($schema);
        $this->createMarelloUPSTransportLabelTable($schema);
        $this->addMarelloUpsTransportShipServiceForeignKeys($schema);
        $this->addMarelloUpsTransportLabelForeignKeys($schema);
        $this->addOroIntegrationTransportForeignKeys($schema);
        $this->addMarelloUPSShippingServiceForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    public function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        if (!$table->hasColumn('ups_test_mode')) {
            $table->addColumn('ups_test_mode', 'boolean', ['notnull' => false, 'default' => false]);
        }
        if (!$table->hasColumn('ups_api_user')) {
            $table->addColumn('ups_api_user', 'string', ['notnull' => false, 'length' => 255]);
        }
        if (!$table->hasColumn('ups_api_password')) {
            $table->addColumn('ups_api_password', 'string', ['notnull' => false, 'length' => 255]);
        }
        if (!$table->hasColumn('ups_api_key')) {
            $table->addColumn('ups_api_key', 'string', ['notnull' => false, 'length' => 255]);
        }
        if (!$table->hasColumn('ups_shipping_account_number')) {
            $table->addColumn('ups_shipping_account_number', 'string', ['notnull' => false, 'length' => 100]);
        }
        if (!$table->hasColumn('ups_shipping_account_name')) {
            $table->addColumn('ups_shipping_account_name', 'string', ['notnull' => false, 'length' => 255]);
        }
        if (!$table->hasColumn('ups_pickup_type')) {
            $table->addColumn('ups_pickup_type', 'string', ['notnull' => false, 'length' => 2]);
        }
        if (!$table->hasColumn('ups_unit_of_weight')) {
            $table->addColumn('ups_unit_of_weight', 'string', ['notnull' => false, 'length' => 3]);
        }
        if (!$table->hasColumn('ups_country_code')) {
            $table->addColumn('ups_country_code', 'string', ['notnull' => false, 'length' => 2]);
        }
        if (!$table->hasColumn('ups_invalidate_cache_at')) {
            $table->addColumn(
                'ups_invalidate_cache_at',
                'datetime',
                ['notnull' => false, 'comment' => '(DC2Type:datetime)']
            );
        }
    }

    /**
     * @param Schema $schema
     */
    public function createMarelloUPSShippingServiceTable(Schema $schema)
    {
        $table = $schema->createTable('marello_ups_shipping_service');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['notnull' => true, 'length' => 10]);
        $table->addColumn('description', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('country_code', 'string', ['length' => 2]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['country_code'], 'IDX_C6DD8778F026BB7C', []);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloUPSTransportShipServiceTable(Schema $schema)
    {
        $table = $schema->createTable('marello_ups_transport_ship_srv');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('ship_service_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'ship_service_id']);
        $table->addIndex(['transport_id'], 'IDX_1554DDE9909C13F', []);
        $table->addIndex(['ship_service_id'], 'IDX_1554DDE37CA9B1D', []);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloUPSTransportLabelTable(Schema $schema)
    {
        $table = $schema->createTable('marello_ups_transport_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addIndex(['transport_id'], 'IDX_1554DDE9909C13D', []);
        $table->addUniqueIndex(['localized_value_id'], 'UNIQ_1554DDE37CA9B1F', []);
    }

    /**
     * @param Schema $schema
     */
    protected function addOroIntegrationTransportForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['ups_country_code'],
            ['iso2_code'],
            ['onUpdate' => null, 'onDelete' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloUPSShippingServiceForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_ups_shipping_service');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onUpdate' => null, 'onDelete' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloUpsTransportShipServiceForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_ups_transport_ship_srv');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_ups_shipping_service'),
            ['ship_service_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloUpsTransportLabelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_ups_transport_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
