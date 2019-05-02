<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_4;

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
        /** Foreign keys generation **/
        $this->addMarelloProductProductForeignKeys($schema);
    }

    /**
     * Add marello_product_product foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloProductProductForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_product_product');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_supplier_supplier'),
            ['preferred_supplier_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
