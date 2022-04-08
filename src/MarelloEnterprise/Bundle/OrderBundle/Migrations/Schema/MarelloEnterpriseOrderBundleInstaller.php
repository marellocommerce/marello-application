<?php

namespace MarelloEnterprise\Bundle\OrderBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;

class MarelloEnterpriseOrderBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->updateMarelloOrderTable($schema);
    }

    /**
     * Create marello_order_order table
     *
     * @param Schema $schema
     */
    protected function updateMarelloOrderTable(Schema $schema)
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
