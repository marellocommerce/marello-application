<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_5;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;

class UpdateAllocationTable implements Migration, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateAllocationTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateAllocationTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_allocation');
        if ($table->hasColumn('comment')) {
            $table->dropColumn('comment');
        }

        if ($table->hasColumn('type')) {
            $table->dropColumn('type');
        }

        $tableName = $this->extendExtension->getNameGenerator()->generateEnumTableName('marello_allocation_reason');
        // enum table is already available and created...
        if ($schema->hasTable($tableName)) {
            return;
        }
        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'allocationContext',
            'marello_allocation_allocationcontext',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
    }

    /**
     * Sets the ExtendExtension
     *
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
