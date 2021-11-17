<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class AddForeignKey implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addMarelloSalesSalesChannelForeignKeys($schema);
    }

    /**
     * Add marello_sales_sales_channel foreign keys.
     *
     * @param Schema $schema
     */
    protected function addMarelloSalesSalesChannelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_sales_channel');
        $table->addForeignKeyConstraint(
            $schema->getTable('marello_sales_channel_type'),
            ['channel_type'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
