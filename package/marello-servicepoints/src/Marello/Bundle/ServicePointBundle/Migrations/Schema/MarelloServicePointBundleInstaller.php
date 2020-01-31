<?php

namespace Marello\Bundle\ServicePointBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloServicePointBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_3';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloSpAddressTable($schema);
        $this->createMarelloSpBhOverrideTable($schema);
        $this->createMarelloSpBusinesshoursTable($schema);
        $this->createMarelloSpFacilityTable($schema);
        $this->createMarelloSpFacilityLabelsTable($schema);
        $this->createMarelloSpServicepointTable($schema);
        $this->createMarelloSpServicepointDescrsTable($schema);
        $this->createMarelloSpServicepointFacTable($schema);
        $this->createMarelloSpServicepointLabelsTable($schema);
        $this->createMarelloSpTimeperiodTable($schema);

        /** Foreign keys generation **/
        $this->addMarelloSpBhOverrideForeignKeys($schema);
        $this->addMarelloSpBusinesshoursForeignKeys($schema);
        $this->addMarelloSpFacilityLabelsForeignKeys($schema);
        $this->addMarelloSpServicepointForeignKeys($schema);
        $this->addMarelloSpServicepointDescrsForeignKeys($schema);
        $this->addMarelloSpServicepointFacForeignKeys($schema);
        $this->addMarelloSpServicepointLabelsForeignKeys($schema);
        $this->addMarelloSpTimeperiodForeignKeys($schema);
    }

    /**
     * Create marello_sp_address table
     *
     * @param Schema $schema
     */
    protected function createMarelloSpAddressTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sp_address');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('street2', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('region_text', 'text', ['notnull' => false, 'length' => 0]);
        $table->addColumn('country_code', 'string', ['length' => 2]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('created_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('serialized_data', 'array', ['notnull' => false, 'length' => 0, 'comment' => '(DC2Type:array)']);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create marello_sp_bh_override table
     *
     * @param Schema $schema
     */
    protected function createMarelloSpBhOverrideTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sp_bh_override');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('servicepoint_facility_id', 'integer', []);
        $table->addColumn('date', 'date', ['length' => 0, 'comment' => '(DC2Type:date)']);
        $table->addColumn('open_status', 'string', ['length' => 6]);
        $table->addColumn('created_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('serialized_data', 'array', ['notnull' => false, 'length' => 0, 'comment' => '(DC2Type:array)']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['date', 'servicepoint_facility_id'], null);
        $table->addIndex(['date'], null, []);
        $table->addIndex(['servicepoint_facility_id'], null, []);
    }

    /**
     * Create marello_sp_businesshours table
     *
     * @param Schema $schema
     */
    protected function createMarelloSpBusinesshoursTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sp_businesshours');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('servicepoint_facility_id', 'integer', []);
        $table->addColumn('day_of_week', 'integer', []);
        $table->addColumn('created_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('serialized_data', 'array', ['notnull' => false, 'length' => 0, 'comment' => '(DC2Type:array)']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['day_of_week', 'servicepoint_facility_id'], null);
        $table->addIndex(['day_of_week'], null, []);
        $table->addIndex(['servicepoint_facility_id'], null, []);
    }

    /**
     * Create marello_sp_facility table
     *
     * @param Schema $schema
     */
    protected function createMarelloSpFacilityTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sp_facility');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['length' => 32]);
        $table->addColumn('serialized_data', 'array', ['notnull' => false, 'length' => 0, 'comment' => '(DC2Type:array)']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'uniq_marello_sp_facility_code');
    }

    /**
     * Create marello_sp_facility_labels table
     *
     * @param Schema $schema
     */
    protected function createMarelloSpFacilityLabelsTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sp_facility_labels');
        $table->addColumn('facility_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['facility_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], null);
        $table->addIndex(['facility_id'], null, []);
    }

    /**
     * Create marello_sp_servicepoint table
     *
     * @param Schema $schema
     */
    protected function createMarelloSpServicepointTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sp_servicepoint');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('address_id', 'integer', []);
        $table->addColumn('latitude', 'decimal', ['notnull' => false, 'scale' => 7]);
        $table->addColumn('longitude', 'decimal', ['notnull' => false, 'scale' => 7]);
        $table->addColumn('image_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('serialized_data', 'array', ['notnull' => false, 'length' => 0, 'comment' => '(DC2Type:array)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['address_id'], null, []);
    }

    /**
     * Create marello_sp_servicepoint_descrs table
     *
     * @param Schema $schema
     */
    protected function createMarelloSpServicepointDescrsTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sp_servicepoint_descrs');
        $table->addColumn('service_point_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['service_point_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], null);
        $table->addIndex(['service_point_id'], null, []);
    }

    /**
     * Create marello_sp_servicepoint_fac table
     *
     * @param Schema $schema
     */
    protected function createMarelloSpServicepointFacTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sp_servicepoint_fac');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('service_point_id', 'integer', []);
        $table->addColumn('facility_id', 'integer', []);
        $table->addColumn('phone', 'text', ['notnull' => false, 'length' => 0]);
        $table->addColumn('email', 'text', ['notnull' => false, 'length' => 0]);
        $table->addColumn('created_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('serialized_data', 'array', ['notnull' => false, 'length' => 0, 'comment' => '(DC2Type:array)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['service_point_id'], null, []);
        $table->addIndex(['facility_id'], null, []);
    }

    /**
     * Create marello_sp_servicepoint_labels table
     *
     * @param Schema $schema
     */
    protected function createMarelloSpServicepointLabelsTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sp_servicepoint_labels');
        $table->addColumn('service_point_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['service_point_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id'], null);
        $table->addIndex(['service_point_id'], null, []);
    }

    /**
     * Create marello_sp_timeperiod table
     *
     * @param Schema $schema
     */
    protected function createMarelloSpTimeperiodTable(Schema $schema)
    {
        $table = $schema->createTable('marello_sp_timeperiod');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('business_hours_id', 'integer', ['notnull' => false]);
        $table->addColumn('business_hours_override_id', 'integer', ['notnull' => false]);
        $table->addColumn('open_time', 'time', ['length' => 0, 'comment' => '(DC2Type:time)']);
        $table->addColumn('close_time', 'time', ['length' => 0, 'comment' => '(DC2Type:time)']);
        $table->addColumn('created_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['length' => 0, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn('serialized_data', 'array', ['notnull' => false, 'length' => 0, 'comment' => '(DC2Type:array)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['open_time', 'close_time'], null, []);
        $table->addIndex(['type'], null, []);
        $table->addIndex(['business_hours_id'], null, []);
        $table->addIndex(['business_hours_override_id'], null, []);
    }

    /**
     * Add marello_sp_bh_override foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSpBhOverrideForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sp_bh_override');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sp_servicepoint_fac'),
            ['servicepoint_facility_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_sp_businesshours foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSpBusinesshoursForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sp_businesshours');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sp_servicepoint_fac'),
            ['servicepoint_facility_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_sp_facility_labels foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSpFacilityLabelsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sp_facility_labels');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sp_facility'),
            ['facility_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_sp_servicepoint foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSpServicepointForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sp_servicepoint');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sp_address'),
            ['address_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_sp_servicepoint_descrs foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSpServicepointDescrsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sp_servicepoint_descrs');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sp_servicepoint'),
            ['service_point_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_sp_servicepoint_fac foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSpServicepointFacForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sp_servicepoint_fac');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sp_servicepoint'),
            ['service_point_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sp_facility'),
            ['facility_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_sp_servicepoint_labels foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSpServicepointLabelsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sp_servicepoint_labels');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sp_servicepoint'),
            ['service_point_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add marello_sp_timeperiod foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSpTimeperiodForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sp_timeperiod');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sp_bh_override'),
            ['business_hours_override_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sp_businesshours'),
            ['business_hours_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
