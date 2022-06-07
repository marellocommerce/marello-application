<?php

namespace MarelloEnterprise\Bundle\InventoryBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;

class AddIsConsolidationWarehouseColumn implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloWarehouseTable($schema);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
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
