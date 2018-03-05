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
        return 'v1_0';
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
        $table->addColumn('marello_magento_infos_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('marello_magento_client_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('marello_magento_client_secret', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('marello_magento_token', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('marello_magento_token_secret', 'string', ['notnull' => false, 'length' => 255]);
    }
}
