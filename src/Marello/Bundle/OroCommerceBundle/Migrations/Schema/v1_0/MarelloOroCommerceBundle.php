<?php

namespace Marello\Bundle\OroCommerceBundle\Migrations\Schema\v1_0;

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
        $this->updateOroIntegrationTransportTable($schema);
    }

    /**
     * @param Schema $schema
     */
    public function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');

        $table->addColumn('orocommerce_url', 'string', ['notnull' => false, 'length' => 1024]);
        $table->addColumn('orocommerce_currency', 'string', ['notnull' => false, 'length' => 3]);
        $table->addColumn('orocommerce_username', 'string', ['notnull' => false, 'length' => 1024]);
        $table->addColumn('orocommerce_key', 'string', ['notnull' => false, 'length' => 1024]);
        $table->addColumn('orocommerce_productunit', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('orocommerce_customertaxcode', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_pricelist', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_productfamily', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_inventorythreshold', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_lowinvthreshold', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_backorder', 'boolean', ['notnull' => false]);
    }
}
