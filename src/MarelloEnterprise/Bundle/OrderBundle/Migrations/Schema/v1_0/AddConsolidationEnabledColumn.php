<?php

namespace MarelloEnterprise\Bundle\OrderBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;

class AddConsolidationEnabledColumn implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_order_order');
        if (!$table->hasColumn('consolidation_enabled')) {
            $table->addColumn(
                'consolidation_enabled',
                'boolean',
                [
                    'notnull' => false,
                    'default' => false,
                    OroOptions::KEY => [
                        'extend' => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM],
                        'form' => ['is_enabled' => true],
                        'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                        'importexport' => ['excluded' => true],
                        ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                    ],
                ]
            );
        }
    }
}
