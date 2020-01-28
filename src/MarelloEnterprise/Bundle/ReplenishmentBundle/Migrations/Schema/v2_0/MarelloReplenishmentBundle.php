<?php

namespace MarelloEnterprise\Bundle\ReplenishmentBundle\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloReplenishmentBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloReplenishmentItemTable($schema);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateMarelloReplenishmentItemTable(Schema $schema)
    {
        $table = $schema->getTable('marello_repl_order_item');
        $table->addColumn('inventory_batches', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
    }
 }
