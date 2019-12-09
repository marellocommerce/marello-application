<?php

namespace Marello\Bundle\ServicePointBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloServicePointBundle implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_sp_timeperiod');
        if ($table->hasIndex('UNIQ_F129BDDB6A791719F5A2460')) {
            $table->dropIndex('UNIQ_F129BDDB6A791719F5A2460');
        }
    }
}
