<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloMagento2Bundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createWebsiteIntegrationStatus($schema);
        $this->createWebsiteIntegrationStatusForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createWebsiteIntegrationStatus(Schema $schema)
    {
        $table = $schema->createTable('marello_m2_webs_integr_status');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('website_id', 'integer');
        $table->addColumn('status_id', 'integer');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['status_id'], 'unq_integration_status');
    }

    /**
     * @param Schema $schema
     */
    protected function createWebsiteIntegrationStatusForeignKeys(Schema $schema)
    {
        $websiteIntegrationTable = $schema->getTable('marello_m2_webs_integr_status');
        $websiteIntegrationTable->addForeignKeyConstraint(
            $schema->getTable('marello_m2_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );

        $websiteIntegrationTable->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel_status'),
            ['status_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }
}
