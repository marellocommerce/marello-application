<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class MarelloSalesBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->modifyMarelloSalesSalesChannelTable($schema);
        $this->modifyMarelloSalesSalesChannelGroupTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function modifyMarelloSalesSalesChannelTable(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_sales_channel');
        // remove foreign key in order to remove unique index
        if ($table->hasForeignKey('FK_37C71D13D6A9E29')) {
            $table->removeForeignKey('FK_37C71D13D6A9E29');
        }

        if ($table->hasIndex('UNIQ_75C456C9F5B7AF7511')) {
            $table->dropIndex('UNIQ_75C456C9F5B7AF7511');
        }

        // add the foreign key back as it does come in handy :D
        if (!$table->hasForeignKey('FK_37C71D13D6A9E29')) {
            $table->addForeignKeyConstraint(
                $schema->getTable('oro_integration_channel'),
                ['integration_channel_id'],
                ['id'],
                ['onDelete' => 'SET NULL', 'onUpdate' => null]
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function modifyMarelloSalesSalesChannelGroupTable(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_channel_group');
        $table->addColumn('integration_channel_id', 'integer', ['notnull' => false]);
        $table->addUniqueIndex(['integration_channel_id'], 'UNIQ_759DCFAB3D6A9E29');

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['integration_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
