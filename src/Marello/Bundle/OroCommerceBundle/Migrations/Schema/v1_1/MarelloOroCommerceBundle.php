<?php

namespace Marello\Bundle\OroCommerceBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloOroCommerceBundle implements Migration
{
    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOroIntegrationTransportTable($schema, $queries);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    public function updateOroIntegrationTransportTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_integration_transport');

        $table->addColumn('orocommerce_enterprise', 'boolean', ['notnull' => false]);
        $table->addColumn('orocommerce_warehouse', 'integer', ['notnull' => false]);

        $query = "
            UPDATE oro_integration_transport
                SET
                    orocommerce_enterprise = false
                WHERE
                    type = 'orocommercesettings'
        ";
        $queries->addQuery($query);
    }
}
