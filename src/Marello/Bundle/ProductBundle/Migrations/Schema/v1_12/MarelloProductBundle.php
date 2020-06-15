<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_12;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloProductBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateMarelloProductProductTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function updateMarelloProductProductTable(Schema $schema)
    {
        $table = $schema->getTable('marello_product_product');
        $table->addColumn('channels_codes', 'text', ['notnull' => false, 'comment' => '(DC2Type:text)']);
        $table->addColumn('categories_codes', 'text', ['notnull' => false, 'comment' => '(DC2Type:text)']);
    }
}
