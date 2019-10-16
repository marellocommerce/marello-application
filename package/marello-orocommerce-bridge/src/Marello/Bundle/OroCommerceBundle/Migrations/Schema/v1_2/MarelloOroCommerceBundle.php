<?php

namespace Marello\Bundle\OroCommerceBundle\Migrations\Schema\v1_2;

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

        $table->addColumn('orocommerce_businessunit', 'integer', ['notnull' => false]);
    }
}
