<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class InventoryBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloWFAStrategyRuleTable($schema);
        $this->addMarelloWFAStrategyRuleForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createMarelloWFAStrategyRuleTable(Schema $schema)
    {
        $table = $schema->createTable('marello_inventory_wfa_rule');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('strategy', 'string', ['notnull' => true, 'length' => 50]);
        $table->addColumn('rule_id', 'integer', []);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }
    
    /**
     * @param Schema $schema
     */
    protected function addMarelloWFAStrategyRuleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_wfa_rule');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_rule'),
            ['rule_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
