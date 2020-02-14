<?php

namespace Marello\Bundle\ServicePointBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloServicePointBundle implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_sp_timeperiod');
        $table->dropColumn('servicepoint_facility_id');
        $table->dropColumn('day_of_week');
    }
}
