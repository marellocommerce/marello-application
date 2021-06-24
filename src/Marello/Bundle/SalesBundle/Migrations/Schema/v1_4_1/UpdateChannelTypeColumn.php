<?php

namespace Marello\Bundle\SalesBundle\Migrations\Schema\v1_4_1;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityBundle\ORM\DatabaseDriverInterface;
use Oro\Bundle\MigrationBundle\Migration\ConnectionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;

/**
 * Class UpdateChannelTypeColumn
 * @package Marello\Bundle\SalesBundle\Migrations\Schema\v1_4_1
 */
class UpdateChannelTypeColumn implements Migration, ConnectionAwareInterface
{
    /** @var Connection */
    private $connection;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('marello_sales_sales_channel');
        if ($table->hasForeignKey('FK_37C71D1108AF457')) {
            if ($this->connection->getDriver()->getName() === DatabaseDriverInterface::DRIVER_POSTGRESQL) {
                $sql = 'CONSTRAINT';
            } else {
                $sql = 'FOREIGN KEY';
            }
            $queries->addPreQuery(
                new ParametrizedSqlMigrationQuery(
                    sprintf('ALTER TABLE marello_sales_sales_channel DROP %s FK_37C71D1108AF457', $sql)
                )
            );
            $queries->addPostQuery(
                new ParametrizedSqlMigrationQuery(
                    'ALTER TABLE marello_sales_sales_channel
                    ADD CONSTRAINT FK_37C71D1108AF457
                    FOREIGN KEY (channel_type) REFERENCES marello_sales_channel_type(name);'
                )
            );
        }
        /** Tables modification **/
        $this->modifyMarelloSalesSalesChannelTable($schema);
    }

    /**
     * {@inheritDoc}
     */
    protected function modifyMarelloSalesSalesChannelTable(Schema $schema)
    {
        $table = $schema->getTable('marello_sales_sales_channel');
        $table->changeColumn('channel_type', ['length' => 64, 'notnull' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }
}
