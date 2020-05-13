<?php

namespace Marello\Bundle\CatalogBundle\Migrations\Schema\v1_1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloCatalogBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->updateCatalogCategoryTable($schema);
    }

    /**
     * Create marello_catalog_category table
     *
     * @param Schema $schema
     */
    protected function updateCatalogCategoryTable(Schema $schema)
    {
        $table = $schema->getTable('marello_catalog_category');
        if ($table->hasIndex('UNIQ_C4B343DF77153098')) {
            $table->renameIndex('UNIQ_C4B343DF77153098', 'marello_catalog_category_codeidx');
        }
    }
}
