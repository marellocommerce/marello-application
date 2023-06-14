<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateAllocationItemTable implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateAllocationItemTable($schema);
    }

    protected function updateAllocationItemTable(Schema $schema)
    {
        // drop old foreign key
        $table = $schema->getTable('marello_inventory_alloc_item');
        foreach ($table->getForeignKeys() as $fk) {
            if ($fk->getForeignTableName() === 'marello_product_product') {
                try {
                    $table->removeForeignKey($fk->getName());
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
        }
        // add new foreign key
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_product_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );

        if ($table->hasColumn('product_id')) {
            $table->changeColumn(
                'product_id',
                ['notnull' => false]
            );
        }
    }
}
