<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Marello\Bundle\Magento2Bundle\Entity\Customer;
use Marello\Bundle\Magento2Bundle\Entity\Product;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloMagento2Bundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_m2_website');
        $table->getColumn('origin_id')->setNotnull(true);

        $table = $schema->getTable('marello_m2_store');
        $table->getColumn('origin_id')->setNotnull(true);

        $table = $schema->getTable('marello_m2_product_tax_class');
        $table->getColumn('origin_id')->setNotnull(true);

        $queries->addQuery(
            new UpdateEntityConfigFieldValueQuery(
                Customer::class,
                'originId',
                'importexport',
                'identity',
                -1
            )
        );

        $queries->addQuery(
            new UpdateEntityConfigFieldValueQuery(
                Product::class,
                'originId',
                'importexport',
                'identity',
                -1
            )
        );
    }
}
