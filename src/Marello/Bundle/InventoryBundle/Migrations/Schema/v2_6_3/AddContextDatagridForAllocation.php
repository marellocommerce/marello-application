<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddContextDatagridForAllocation implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                'Marello\Bundle\InventoryBundle\Entity\Allocation',
                'grid',
                'context',
                'marello-allocation-for-context-grid'
            )
        );
    }
}
