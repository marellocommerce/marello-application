<?php

namespace Marello\Bundle\RuleBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloRuleBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloRuleTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloRuleTable(Schema $schema)
    {
        $table = $schema->createTable('marello_rule');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'text', []);
        $table->addColumn('enabled', 'boolean', ['default' => true]);
        $table->addColumn('sort_order', 'integer', []);
        $table->addColumn('stop_processing', 'boolean', ['default' => false]);
        $table->addColumn('system', 'boolean', ['default' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['created_at'], 'idx_marello_rule_created_at', []);
        $table->addIndex(['updated_at'], 'idx_marello_rule_updated_at', []);
    }
}
