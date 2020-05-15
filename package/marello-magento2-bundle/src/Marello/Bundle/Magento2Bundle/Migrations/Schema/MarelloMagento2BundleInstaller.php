<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloMagento2BundleInstaller implements Installation
{
    /**
     * {@inheritDoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateIntegrationTransportTable($schema);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('api_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('api_token', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('sync_start_date', 'date', ['notnull' => false]);
        $table->addColumn('sync_range', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('initial_sync_start_date', 'datetime', ['notnull' => false]);
        $table->addColumn('websites', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
    }
}
