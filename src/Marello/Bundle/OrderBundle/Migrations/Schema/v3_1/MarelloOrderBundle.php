<?php

namespace Marello\Bundle\OrderBundle\Migrations\Schema\v3_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloOrderBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOrderTable($schema);
    }

    /**
     * @param Schema $schema
     */
    public function updateOrderTable(Schema $schema)
    {
        $table = $schema->getTable('marello_order_order');
        $table->dropColumn('payment_reference');
        $table->dropColumn('payment_details');
    }
}
