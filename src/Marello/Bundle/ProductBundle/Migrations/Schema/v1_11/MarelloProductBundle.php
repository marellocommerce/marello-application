<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class MarelloProductBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->changeMarelloProductProductUniqueIndex($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function changeMarelloProductProductUniqueIndex(Schema $schema)
    {
        $table = $schema->getTable('marello_product_product');
        $table->dropIndex('marello_product_product_skuidx');
        $table->addUniqueIndex(['sku', 'organization_id'], 'marello_product_product_skuorgidx');
    }
}
