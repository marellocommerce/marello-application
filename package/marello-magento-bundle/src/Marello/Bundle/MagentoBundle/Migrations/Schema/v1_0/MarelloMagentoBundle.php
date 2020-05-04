<?php

namespace MarelloMagentoBundle\src\Marello\Bundle\MagentoBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloMagentoBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateProductOriginId($schema);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public static function updateProductOriginId(Schema $schema)
    {
        $table = $schema->getTable('marello_magento_product');
        $table->changeColumn('origin_id', ['notnull' => false]);
    }
}
