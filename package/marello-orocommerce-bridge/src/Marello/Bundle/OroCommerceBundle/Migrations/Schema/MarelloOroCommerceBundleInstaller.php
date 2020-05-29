<?php

namespace Marello\Bundle\OroCommerceBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloOroCommerceBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_4';
    }

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
        $table->addColumn('orocommerce_enterprise', 'boolean', ['notnull' => false]);
        $table->addColumn('orocommerce_warehouse', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_businessunit', 'integer', ['notnull' => false]);
        $table->addColumn('orocommerce_deldataondeactiv', 'boolean', ['notnull' => false]);
        $table->addColumn('orocommerce_deldataondel', 'boolean', ['notnull' => false]);
        $table->addColumn('orocommerce_data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);
    }
}
