<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_8;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateBalancedInventoryTable implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateBalancedInventoryTable($schema);
    }

    protected function updateBalancedInventoryTable(Schema $schema)
    {
        // drop old foreign key
        $table = $schema->getTable('marello_blncd_inventory_level');
        foreach ($table->getForeignKeys() as $fk) {
            if ($fk->getForeignTableName() === 'marello_sales_channel_group') {
                try {
                    $table->removeForeignKey($fk->getName());
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
        // add new foreign key
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_channel_group'),
            ['channel_group_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
