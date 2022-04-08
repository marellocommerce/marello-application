<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v3_1_3;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;

use Marello\Bundle\OrderBundle\Entity\Order;

class MarelloOrderBundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_order_order');
        if (!$table->hasColumn('consolidation_enabled')) {
            $table->addColumn('consolidation_enabled', 'boolean', ['notnull' => false, 'default' => false]);
        }
    }
}
