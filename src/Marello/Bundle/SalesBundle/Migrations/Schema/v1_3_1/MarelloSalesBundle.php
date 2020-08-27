<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_3_1;

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
        $this->modifyMarelloSalesSalesChannelGroupTable($schema);
    }

    /**
     * {@inheritDoc}
     */
    protected function modifyMarelloSalesSalesChannelGroupTable(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_channel_group');
        if (!$table->hasColumn('integration_channel_id')) {
            $table->addColumn('integration_channel_id', 'integer', ['notnull' => false]);
        }

        if (!$table->hasIndex('UNIQ_759DCFAB3D6A9E29')) {
            $table->addUniqueIndex(['integration_channel_id'], 'UNIQ_759DCFAB3D6A9E29');
        }

        if ($table->hasForeignKey('FK_759DCFAB3D6A9E29')) {
            $table->addForeignKeyConstraint(
                $schema->getTable('oro_integration_channel'),
                ['integration_channel_id'],
                ['id'],
                ['onDelete' => 'SET NULL', 'onUpdate' => null]
            );
        }
    }
}
