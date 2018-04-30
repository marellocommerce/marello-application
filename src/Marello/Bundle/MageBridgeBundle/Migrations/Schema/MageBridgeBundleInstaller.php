<?php

namespace Oro\Bundle\MageBridgeBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MageBridgeBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOroIntegrationTransportTable($schema);
    }

    public function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('api_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('admin_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('client_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('client_secret', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('token_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('token_secret', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('salesChannels', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
    }
}
