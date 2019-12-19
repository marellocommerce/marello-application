<?php

namespace Marello\Bundle\ServicePointBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Marello\Bundle\ServicePointBundle\Entity\TimePeriod;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloServicePointBundle implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->removeForeignKeys($schema, $queries);
        $this->addBusinessHoursTable($schema, $queries);
        $this->addBusinessHoursOverrideTable($schema, $queries);
        $this->updateTimePeriodTable($schema, $queries);
        $this->addForeignKeys($schema, $queries);
    }

    protected function removeForeignKeys(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_sp_timeperiod');
        if ($table->hasForeignKey('FK_F129BDDB9F5A2460')) {
            $table->removeForeignKey('FK_F129BDDB9F5A2460');
        }
        if ($table->hasIndex('IDX_F129BDDB6A79171')) {
            $table->dropIndex('IDX_F129BDDB6A79171');
        }
        if ($table->hasIndex('IDX_F129BDDB9F5A2460')) {
            $table->dropIndex('IDX_F129BDDB9F5A2460');
        }
    }

    protected function addBusinessHoursTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('marello_sp_businesshours');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('servicepoint_facility_id', 'integer');
        $table->addColumn('day_of_week', 'integer');
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['day_of_week']);
        $table->addUniqueIndex(['day_of_week', 'servicepoint_facility_id']);

        $queries->addPostQuery('INSERT INTO marello_sp_businesshours (servicepoint_facility_id, day_of_week, created_at, updated_at) SELECT DISTINCT servicepoint_facility_id, day_of_week, NOW(), NOW() FROM marello_sp_timeperiod');
    }

    protected function addBusinessHoursOverrideTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('marello_sp_bh_override');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('servicepoint_facility_id', 'integer');
        $table->addColumn('date', 'date');
        $table->addColumn('open_status', 'string', ['length' => 6]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['date']);
        $table->addUniqueIndex(['date', 'servicepoint_facility_id']);
    }

    protected function updateTimePeriodTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_sp_timeperiod');
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn('business_hours_id', 'integer', ['notnull' => false]);
        $table->addColumn('business_hours_override_id', 'integer', ['notnull' => false]);
        $table->changeColumn('servicepoint_facility_id', ['notnull' => false]);
        $table->addIndex(['type']);

        $queries->addPostQuery('UPDATE marello_sp_timeperiod a LEFT JOIN marello_sp_businesshours b ON a.servicepointfacility_id = b.servicepointfacility_id AND a.date = b.date SET a.business_hours_id = b.id');
        $queries->addQuery(new RemoveFieldQuery(TimePeriod::class, 'servicepointFacility'));
        $queries->addQuery(new RemoveFieldQuery(TimePeriod::class, 'dayOfWeek'));
    }

    protected function addForeignKeys(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_sp_businesshours');
        $table->addForeignKeyConstraint(
            'marello_sp_servicepoint_fac',
            ['servicepoint_facility_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $table = $schema->getTable('marello_sp_bh_override');
        $table->addForeignKeyConstraint(
            'marello_sp_servicepoint_fac',
            ['servicepoint_facility_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $table = $schema->getTable('marello_sp_timeperiod');
        $table->addForeignKeyConstraint(
            'marello_sp_businesshours',
            ['business_hours_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            'marello_sp_bh_override',
            ['business_hours_override_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }
}
