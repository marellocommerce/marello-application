<?php

namespace Marello\Bundle\Magento2Bundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;
use Marello\Bundle\Magento2Bundle\Entity\Customer;
use Marello\Bundle\Magento2Bundle\Entity\Product;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloMagento2Bundle implements Migration
{
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
        if (!$table->hasColumn('m2_del_remote_prod_webs_only')) {
            $table->addColumn('m2_del_remote_prod_webs_only', 'boolean', ['notnull' => false]);
        }
    }
}
