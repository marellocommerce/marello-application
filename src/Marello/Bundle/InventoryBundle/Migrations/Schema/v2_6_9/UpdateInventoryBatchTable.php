<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateInventoryBatchTable implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_inventory_batch');
        $table->addColumn('sell_by_date', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
    }
}
