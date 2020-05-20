<?php

namespace Marello\Bundle\CatalogBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class MarelloCatalogBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->changeMarelloCatalogCategoryUniqueIndex($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function changeMarelloCatalogCategoryUniqueIndex(Schema $schema)
    {
        $table = $schema->getTable('marello_catalog_category');
        $table->dropIndex('marello_catalog_category_codeidx');
        $table->addUniqueIndex(['code', 'organization_id'], 'marello_catalog_category_codeorgidx');
    }
}
