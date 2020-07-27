<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class AddChannelTypeTable implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createMarelloSalesChannelTypeTable($schema);
        $queries->addPostQuery(
            new ParametrizedSqlMigrationQuery(
                'INSERT INTO marello_sales_channel_type (
                    name,
                    label
                )
                SELECT DISTINCT
                    channel_type as name,
                    CONCAT(UPPER(LEFT(channel_type, 1)), SUBSTRING(channel_type, 2)) as label
                FROM marello_sales_sales_channel'
            )
        );
    }

    /**
     * Create marello_inventory_wh_type table
     *
     * @param Schema $schema
     */
    protected function createMarelloSalesChannelTypeTable(Schema $schema)
    {
        if (!$schema->hasTable('marello_sales_channel_type')) {
            $table = $schema->createTable('marello_sales_channel_type');
            $table->addColumn('name', 'string', ['length' => 64]);
            $table->addColumn('label', 'string', ['length' => 255]);
            $table->setPrimaryKey(['name']);
            $table->addUniqueIndex(['label'], 'UNIQ_629E2BBEA750E8123');
        }
    }
}
