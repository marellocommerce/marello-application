<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class InventoryBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createMarelloWFAStrategyRuleTable($schema);
        $this->addMarelloWFAStrategyRuleForeignKeys($schema);
        $this->updateMarelloWarehouseTable($schema);
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

    protected function updateMarelloWarehouseTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_warehouse');
        if (!$table->hasColumn('is_consolidation_warehouse')) {
            $table->addColumn(
                'is_consolidation_warehouse',
                'boolean',
                [
                    'notnull' => false,
                    'default' => false,
                    OroOptions::KEY => [
                        'extend' => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM],
                        'form' => ['is_enabled' => false],
                        'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                        'importexport' => ['excluded' => true],
                        ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                    ],
                ]
            );
        }
    }
}
