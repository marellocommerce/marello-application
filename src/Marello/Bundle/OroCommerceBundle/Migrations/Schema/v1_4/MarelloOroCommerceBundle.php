<?php

namespace Marello\Bundle\OroCommerceBundle\Migrations\Schema\v1_4;

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
        $updateQueries = [];
        if ($table->hasColumn('orocommerce_inventorythreshold')) {
            $updateQueries[] = "
            UPDATE oro_integration_transport
                SET
                    orocommerce_inventorythreshold = 0
                WHERE
                    type = 'orocommercesettings'
            ";
        }
        if ($table->hasColumn('orocommerce_lowinvthreshold')) {
            $updateQueries[] = "
            UPDATE oro_integration_transport
                SET
                    orocommerce_lowinvthreshold = 0
                WHERE
                    type = 'orocommercesettings'
            ";
        }
        if ($table->hasColumn('orocommerce_backorder')) {
            $updateQueries[] = "
            UPDATE oro_integration_transport
                SET
                    orocommerce_backorder = false
                WHERE
                    type = 'orocommercesettings'
            ";
        }

        foreach ($updateQueries as $query) {
            $queries->addQuery($query);
        }
    }
}
