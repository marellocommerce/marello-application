<?php

namespace Marello\Bundle\ProductBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Marello\Bundle\ProductBundle\Migrations\Schema\MarelloProductBundleInstaller;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemovePriceAndCostColumns implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(MarelloProductBundleInstaller::PRODUCT_TABLE);
        if ($table->hasColumn('price')) {
            $table->dropColumn('price');
        }
        if ($table->hasColumn('cost')) {
            $table->dropColumn('cost');
        }
        $table->changeColumn('updated_at', ['notnull' => false]);
    }
}
