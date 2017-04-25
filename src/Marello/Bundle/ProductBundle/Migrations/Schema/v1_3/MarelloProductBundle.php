<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
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
        /** Tables generation **/
        $this->updateDatetimeMarelloProductTable($schema, $queries);
    }

    /**
     * Update marello_product_product table
     *
     * @param Schema $schema
     */
    protected function updateDatetimeMarelloProductTable(Schema $schema, QueryBag $queries)
    {
        $queries->addPreQuery('UPDATE marello_product_product SET updated_at=NOW()');
        $table = $schema->getTable('marello_product_product');
        $table->getColumn('updated_at')->setNotnull(true);
    }
}
