<?php

namespace Marello\Bundle\InventoryBundle\Migrations\Schema\v2_6_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class UpdateAllocationDraftTable implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addMarelloInventoryAllocationDraft($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function addMarelloInventoryAllocationDraft(Schema $schema)
    {
        $table = $schema->getTable('marello_inventory_alloc_draft');
        if (!$table->hasColumn('type')) {
            $table->addColumn('type', 'string', ['notnull' => false]);
        }
    }
}
