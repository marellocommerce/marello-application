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

        $table->addColumn('orocommerce_deldataondeactiv', 'boolean', ['notnull' => false]);
        $table->addColumn('orocommerce_deldataondel', 'boolean', ['notnull' => false]);
        $table->addColumn('orocommerce_data', 'json_array', ['notnull' => false, 'comment' => '(DC2Type:json_array)']);

        $query = "
            UPDATE oro_integration_transport it
                SET
                    it.orocommerce_deldataondeactiv = false,
                    it.orocommerce_deldataondel = false
                WHERE
                    it.type = 'orocommercesettings'
        ";
        $queries->addQuery($query);
    }
}
