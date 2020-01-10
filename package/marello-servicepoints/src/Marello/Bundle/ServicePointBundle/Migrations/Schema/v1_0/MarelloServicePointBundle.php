<?php

namespace Marello\Bundle\ServicePointBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloServicePointBundle implements Migration
{
    const TABLE_FACILITY = 'marello_sp_facility';
    const TABLE_FACILITY_LABELS = 'marello_sp_facility_labels';
    const TABLE_SERVICEPOINT = 'marello_sp_servicepoint';
    const TABLE_SERVICEPOINT_FACILITY = 'marello_sp_servicepoint_fac';
    const TABLE_SERVICEPOINT_ADDRESS = 'marello_sp_address';
    const TABLE_SERVICEPOINT_LABELS = 'marello_sp_servicepoint_labels';
    const TABLE_SERVICEPOINT_DESCRIPTIONS = 'marello_sp_servicepoint_descrs';
    const TABLE_TIMEPERIOD = 'marello_sp_timeperiod';

    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createFacilityTable($schema, $queries);
        $this->createServicePointAddressTable($schema, $queries);
        $this->createServicePointTable($schema, $queries);
        $this->createServicePointFacilityTable($schema, $queries);
        $this->createTimePeriodTable($schema, $queries);

        $this->createForeignKeys($schema, $queries);
    }

    protected function createFacilityTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable(self::TABLE_FACILITY);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['length' => 32]);
        $table->setPrimaryKey(['id']);

        $table->addUniqueIndex(['code']);

        $this->createFacilityLabelsTable($schema, $queries);
    }

    protected function createServicePointAddressTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable(self::TABLE_SERVICEPOINT_ADDRESS);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('street', 'string', ['length' => 500, 'notnull' => false]);
        $table->addColumn('street2', 'string', ['length' => 500, 'notnull' => false]);
        $table->addColumn('city', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('postal_code', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('organization', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('region_text', 'text', ['notnull' => false]);
        $table->addColumn('country_code', 'string', ['length' => 2]);
        $table->addColumn('region_code', 'string', ['length' => 16, 'notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);
    }

    protected function createServicePointTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable(self::TABLE_SERVICEPOINT);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('address_id', 'integer');
        $table->addColumn('latitude', 'decimal', ['precision' => 10, 'scale' => 7]);
        $table->addColumn('longitude', 'decimal', ['precision' => 10, 'scale' => 7]);
        $table->addColumn('image_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);

        $this->createServicePointLabelsTable($schema, $queries);
        $this->createServicePointDescriptionsTable($schema, $queries);
    }

    protected function createServicePointFacilityTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable(self::TABLE_SERVICEPOINT_FACILITY);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('service_point_id', 'integer');
        $table->addColumn('facility_id', 'integer');
        $table->addColumn('phone', 'text', ['notnull' => false]);
        $table->addColumn('email', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);

        $table->addIndex(['service_point_id']);
        $table->addIndex(['facility_id']);
    }

    protected function createTimePeriodTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable(self::TABLE_TIMEPERIOD);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('servicepoint_facility_id', 'integer');
        $table->addColumn('day_of_week', 'integer');
        $table->addColumn('open_time', 'time');
        $table->addColumn('close_time', 'time');
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);

        $table->addIndex(['day_of_week']);
        $table->addIndex(['servicepoint_facility_id']);
        $table->addIndex(['open_time', 'close_time']);
    }

    protected function createFacilityLabelsTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable(self::TABLE_FACILITY_LABELS);
        $table->addColumn('facility_id', 'integer');
        $table->addColumn('localized_value_id', 'integer');
        $table->setPrimaryKey(['facility_id', 'localized_value_id']);

        $table->addUniqueIndex(['localized_value_id']);
        $table->addIndex(['facility_id']);
    }

    protected function createServicePointLabelsTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable(self::TABLE_SERVICEPOINT_LABELS);
        $table->addColumn('service_point_id', 'integer');
        $table->addColumn('localized_value_id', 'integer');
        $table->setPrimaryKey(['service_point_id', 'localized_value_id']);

        $table->addUniqueIndex(['localized_value_id']);
        $table->addIndex(['service_point_id']);
    }

    protected function createServicePointDescriptionsTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable(self::TABLE_SERVICEPOINT_DESCRIPTIONS);
        $table->addColumn('service_point_id', 'integer');
        $table->addColumn('localized_value_id', 'integer');
        $table->setPrimaryKey(['service_point_id', 'localized_value_id']);

        $table->addUniqueIndex(['localized_value_id']);
        $table->addIndex(['service_point_id']);
    }

    protected function createForeignKeys(Schema $schema, QueryBag $queries)
    {
        $servicePointTable = $schema->getTable(self::TABLE_SERVICEPOINT);
        $servicePointFacilityTable = $schema->getTable(self::TABLE_SERVICEPOINT_FACILITY);
        $servicePointLabelsTable = $schema->getTable(self::TABLE_SERVICEPOINT_LABELS);
        $servicePointDescriptionsTable = $schema->getTable(self::TABLE_SERVICEPOINT_DESCRIPTIONS);
        $facilityLabelsTable = $schema->getTable(self::TABLE_FACILITY_LABELS);
        $timePeriodTable = $schema->getTable(self::TABLE_TIMEPERIOD);

        $servicePointLabelsTable->addForeignKeyConstraint(
            self::TABLE_SERVICEPOINT,
            ['service_point_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $servicePointLabelsTable->addForeignKeyConstraint(
            'oro_fallback_localization_val',
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $servicePointDescriptionsTable->addForeignKeyConstraint(
            self::TABLE_SERVICEPOINT,
            ['service_point_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $servicePointDescriptionsTable->addForeignKeyConstraint(
            'oro_fallback_localization_val',
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $facilityLabelsTable->addForeignKeyConstraint(
            self::TABLE_FACILITY,
            ['facility_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $facilityLabelsTable->addForeignKeyConstraint(
            'oro_fallback_localization_val',
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $timePeriodTable->addForeignKeyConstraint(
            self::TABLE_SERVICEPOINT_FACILITY,
            ['servicepoint_facility_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $servicePointFacilityTable->addForeignKeyConstraint(
            self::TABLE_FACILITY,
            ['facility_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $servicePointFacilityTable->addForeignKeyConstraint(
            self::TABLE_SERVICEPOINT,
            ['service_point_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $servicePointTable->addForeignKeyConstraint(
            self::TABLE_SERVICEPOINT_ADDRESS,
            ['address_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }
}
