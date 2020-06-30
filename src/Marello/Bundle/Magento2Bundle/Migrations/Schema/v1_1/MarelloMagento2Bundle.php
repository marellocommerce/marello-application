<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @todo Remove this in final version
 */
class MarelloMagento2Bundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_m2_product');
        $table->addColumn('sku', 'string', ['notnull' => false, 'length' => 255]);
    }
}
