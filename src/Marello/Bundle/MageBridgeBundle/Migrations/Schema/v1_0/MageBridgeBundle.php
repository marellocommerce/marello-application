<?php

namespace Marello\Bundle\ZendeskBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MageBridgeBundle implements Migration
{
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
