<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_7;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateAllocationTable implements Migration, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateAllocationTable($schema);
    }

    protected function updateAllocationTable(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_allocation');

        $tableName = $this->extendExtension->getNameGenerator()->generateEnumTableName('marello_allocation_reshipmentreason');
        if ($schema->hasTable($tableName)) {
            return;
        }

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'reshipmentReason',
            'marello_allocation_reshipmentreason',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
            ]
        );
    }

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
