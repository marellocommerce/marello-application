<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

class MarelloProductBundle implements Migration
{
    const PRODUCT_TABLE = 'marello_product_product';

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Add attribute family and attribute family relation **/
        $this->addAttributeFamily($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function addAttributeFamily(Schema $schema)
    {
        $table = $schema->getTable(self::PRODUCT_TABLE);
        $table->addColumn('attribute_family_id', 'integer', ['notnull' => false]);
        $table->addIndex(['attribute_family_id']);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_attribute_family'),
            ['attribute_family_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'RESTRICT']
        );
    }
}
